<?php

namespace CamassoMedelago\DriveMeSafely\Service;

use CamassoMedelago\DriveMeSafely\Entity\ELezioneTeoria;
use CamassoMedelago\DriveMeSafely\Entity\ELezionePratica;
use CamassoMedelago\DriveMeSafely\Entity\EPrenotazioneLezione;
use CamassoMedelago\DriveMeSafely\Entity\StatoPrenotazione;
use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FLezione;
use CamassoMedelago\DriveMeSafely\Foundation\FPrenotazioneEsami;
use CamassoMedelago\DriveMeSafely\Foundation\FPrenotazioneLezione;
use Doctrine\ORM\EntityManagerInterface;

//Service che implementa la logica per la prenotazione/cancellazione delle lezioni
class SPrenotazioneLezione
{
    private FLezione $fLezione;
    private FIscritto $fIscritto;
    private FPrenotazioneLezione $fPrenotazione;
    private FPrenotazioneEsami $fPrenotazioneEsami;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->fLezione = new FLezione($em);
        $this->fIscritto = new FIscritto($em);
        $this->fPrenotazione = new FPrenotazioneLezione($em);
        $this->fPrenotazioneEsami = new FPrenotazioneEsami($em);
    }

    //1)Metodo che restituisce le lezioni disponibili e e le prenotazioni dell'utente
    public function getCalendarioAllievo(int $idIscritto): array
    {
        if ($idIscritto <= 0 || $this->fIscritto->findById($idIscritto) === null) {
            throw new \InvalidArgumentException('Allievo non trovato nel sistema.');
        }

        return [
            'storicoPrenotazioni' => $this->fPrenotazione->findByIscrittoId($idIscritto),
            'lezioniDisponibili' => $this->fPrenotazione->findLezioniDisponibili($idIscritto),
        ];
    }

    //2)Metodo che restusuisce la prenotazione della lezione
    public function prenotaLezione(int $idIscritto, int $idLezione, ?string $tipoAtteso = null): EPrenotazioneLezione
    {
        if ($idIscritto <= 0 || $idLezione <= 0) {
            throw new \InvalidArgumentException('Parametri di prenotazione non validi.');
        }

        $this->em->beginTransaction();

        try {
            // 1) Verifica utente
            $iscritto = $this->fIscritto->findById($idIscritto);
            if ($iscritto === null) {
                throw new \InvalidArgumentException('Allievo non trovato nel sistema.');
            }

            // 2) Carichiamo lo slot con lock: il controllo e il salvataggio
            // diventano così un'unica operazione atomica.
            $lezione = $this->fLezione->findByIdForUpdate($idLezione);
            if ($lezione === null || $lezione->getDataOra() <= new \DateTimeImmutable()) {
                throw new \InvalidArgumentException('Lo slot selezionato non è più disponibile.');
            }

            // Verifica corrispondenza tipologia richiesta (se specificata)
            if ($tipoAtteso !== null) {
                $tipoAttesoUpper = strtoupper(trim($tipoAtteso));
                if ($tipoAttesoUpper === 'TEORIA' && !($lezione instanceof ELezioneTeoria)) {
                    throw new \InvalidArgumentException('La lezione selezionata non è una lezione teorica.');
                }
                if ($tipoAttesoUpper === 'PRATICA' && !($lezione instanceof ELezionePratica)) {
                    throw new \InvalidArgumentException('La lezione selezionata non è una guida pratica.');
                }
            }

            // 3) Controlli comuni
            if ($this->fPrenotazione->haLezioneInOrario($idIscritto, $lezione->getDataOra())) {
                throw new \InvalidArgumentException('Hai già una lezione prenotata in questo stesso orario.');
            }

            if ($this->fPrenotazioneEsami->haEsameInOrario($idIscritto, $lezione->getDataOra())) {
                throw new \InvalidArgumentException('Hai già una sessione d\'esame prenotata in questo stesso orario.');
            }

            // 4) Controlli specifici per tipo di lezione
            if ($lezione instanceof ELezioneTeoria) {
                if ($this->fPrenotazione->isAulaPiena($idLezione, $lezione->getAula()->getCapienzaMassima())) {
                    throw new \InvalidArgumentException('L\'aula ha raggiunto la capienza massima.');
                }
            } elseif ($lezione instanceof ELezionePratica) {
                if ($this->fPrenotazione->isGuidaPraticaPrenotata($idLezione)) {
                    throw new \InvalidArgumentException('Questa guida pratica è stata appena prenotata da un altro allievo.');
                }
            } else {
                throw new \InvalidArgumentException('Tipo di lezione non riconosciuto.');
            }

            // 5) Idempotenza / Ri-prenotazione: se esisteva già una prenotazione pregressa per questo iscritto e lezione, riattiviamola
            $prenotazioneEsistente = $this->fPrenotazione->findByIscrittoAndLezione($idIscritto, $idLezione);
            if ($prenotazioneEsistente !== null) {
                if ($prenotazioneEsistente->getStato() === StatoPrenotazione::PRENOTATA) {
                    throw new \InvalidArgumentException('Risulti già prenotato a questa lezione.');
                }
                $prenotazioneEsistente->setStato(StatoPrenotazione::PRENOTATA);
                $prenotazioneEsistente->setDataPrenotazione(new \DateTimeImmutable());
                $prenotazioneEsistente->setPresente(null);
                $this->fPrenotazione->save($prenotazioneEsistente);
                $this->em->commit();
                return $prenotazioneEsistente;
            }

            $prenotazione = EPrenotazioneLezione::crea($iscritto, $lezione);
            $this->fPrenotazione->save($prenotazione);
            $this->em->commit();

            return $prenotazione;
        } catch (\Throwable $e) {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->rollback();
            }
            throw $e;
        }
    }

    //3)Metodo che restituisce la cancellazione di una lezione con controlli di sicurezza, preavviso e autorizzazione
    public function annullaPrenotazione(int $idPrenotazione, ?int $idIscrittoRichiedente = null): void
    {
        $this->em->beginTransaction();
        try {
            $prenotazione = $this->fPrenotazione->findById($idPrenotazione);
            if ($prenotazione === null) {
                throw new \InvalidArgumentException('Prenotazione non trovata nel sistema.');
            }

            // Controllo autorizzazione (Anti-IDOR): se specificato, l'iscritto richiedente deve coincidere col proprietario
            if ($idIscrittoRichiedente !== null && $prenotazione->getIscritto()->getId() !== $idIscrittoRichiedente) {
                throw new \InvalidArgumentException('Non sei autorizzato ad annullare questa prenotazione.');
            }

            // Verifica che la prenotazione sia in stato PRENOTATA prima di annullarla
            if ($prenotazione->getStato() !== StatoPrenotazione::PRENOTATA) {
                throw new \InvalidArgumentException('È possibile annullare solo prenotazioni attive.');
            }

            $lezione = $prenotazione->getLezione();
            $adesso = new \DateTimeImmutable();

            // Blocco annullamento per lezioni già passate
            if ($lezione->getDataOra() <= $adesso) {
                throw new \InvalidArgumentException('Non è possibile annullare una lezione già iniziata o passata.');
            }

            // Controllo preavviso minimo: almeno 2 ore prima dell'inizio della lezione
            $limiteAnnullamento = $adesso->modify('+2 hours');
            if ($lezione->getDataOra() < $limiteAnnullamento) {
                throw new \InvalidArgumentException('L’annullamento deve essere effettuato con almeno 2 ore di preavviso rispetto all’inizio della lezione.');
            }

            // Cambia lo stato a ANNULLATA e persisti
            $prenotazione->setStato(StatoPrenotazione::ANNULLATA);
            $this->fPrenotazione->save($prenotazione);
            $this->em->commit();
        } catch (\Throwable $e) {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->rollback();
            }
            throw $e;
        }
    }

    //4)Metodo che conferma la prenotazione di una lezione
    public function conferma(EPrenotazioneLezione $prenotazione): void
    {
        $this->em->beginTransaction();
        try {
            $this->fPrenotazione->save($prenotazione);
            $this->em->commit();
        } catch (\Throwable $e) {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->rollback();
            }
            throw $e;
        }
    }


    //Metodi ausiliari

    public function getPalinsesto(): array
    {
        return $this->fLezione->findAllPalinsesto();
    }

    public function getPrenotazioniLezione(int $idLezione): array
    {
        return $this->fPrenotazione->findByLezioneId($idLezione);
    }
}
