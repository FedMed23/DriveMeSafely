<?php

namespace CamassoMedelago\DriveMeSafely\Service;

use CamassoMedelago\DriveMeSafely\Entity\EDipendente;
use CamassoMedelago\DriveMeSafely\Entity\EEsame;
use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Entity\EPrenotazioneEsami;
use CamassoMedelago\DriveMeSafely\Entity\TipologiaEsame;
use CamassoMedelago\DriveMeSafely\Foundation\FEsame;
use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FPrenotazioneEsami;
use CamassoMedelago\DriveMeSafely\Foundation\FPrenotazioneLezione;
use CamassoMedelago\DriveMeSafely\Foundation\FQuiz;
use CamassoMedelago\DriveMeSafely\Foundation\FSvolgimentoQuiz;
use CamassoMedelago\DriveMeSafely\Foundation\FUtenteRegistrato;
use Doctrine\ORM\EntityManagerInterface;

//Service che implementa la logica per gestire le prenotazioni agli esami della motorizzazione
class SPrenotazioneEsame
{
    private FEsame $fEsame;
    private FIscritto $fIscritto;
    private FUtenteRegistrato $fDipendente;
    private FPrenotazioneEsami $fPrenotazioni;
    private FPrenotazioneLezione $fPrenotazioneLezione;
    private FQuiz $fQuiz;
    private FSvolgimentoQuiz $fSvolgimenti;

    public function __construct(private EntityManagerInterface $em)
    {
        $this->fEsame = new FEsame($em);
        $this->fIscritto = new FIscritto($em);
        $this->fDipendente = new FUtenteRegistrato($em);
        $this->fPrenotazioni = new FPrenotazioneEsami($em);
        $this->fPrenotazioneLezione = new FPrenotazioneLezione($em);
        $this->fQuiz = new FQuiz($em);
        $this->fSvolgimenti = new FSvolgimentoQuiz($em);
    }

    //1)Metodo che riporta l'intero calendario delle prenotazioni ed esami disponibili
    public function getCalendario(): array
    {
        return ['storicoPrenotazioni' => $this->fPrenotazioni->findAll(), 'esamiDisponibili' => $this->fEsame->findSessioniFuture()];
    }

    //2)Metodo che riporta gli iscritti idonei e disponibili per un determinato esame
    public function getIscrittiIdonei(int $idEsame): array
    {
        if ($idEsame <= 0) {
            throw new \InvalidArgumentException('Identificativo esame non valido.');
        }

        //Controllo sull'esame
        $esame = $this->fEsame->findById($idEsame);
        if (!$esame) {
            throw new \InvalidArgumentException('Sessione d\'esame non trovata.');
        }

        if ($esame->getDataEs() <= new \DateTime()) {
            throw new \InvalidArgumentException('La sessione d\'esame selezionata è già iniziata o trascorsa.');
        }

        $risultato = [];
        $tipoEsame = $esame->getTipologia();
        $idoneitaService = $this->creaIdoneitaService();

        // Ai requisiti didattici si aggiungono i vincoli specifici della prenotazione.
        foreach ($idoneitaService->getIscrittiIdonei($idEsame) as $iscritto) {
            $idIscritto = $iscritto->getId();

            // Escludi se l'allievo ha già superato questa tipologia di esame
            if ($tipoEsame === TipologiaEsame::TEORIA && $this->fPrenotazioni->haSuperatoEsameTeorico($idIscritto)) {
                continue;
            }
            if ($tipoEsame === TipologiaEsame::PRATICA && $this->fPrenotazioni->haSuperatoEsamePratico($idIscritto)) {
                continue;
            }

            // Escludi se l'allievo è già attivamente prenotato a questa sessione d'esame
            if ($this->fPrenotazioni->isIscrittoGiaPrenotatoAdEsame($idIscritto, $idEsame)) {
                continue;
            }

            // Escludi se l'allievo ha già un'altra sessione futura attiva per la stessa tipologia
            if ($this->fPrenotazioni->haPrenotazioneFuturaAttivaPerTipologia($idIscritto, $tipoEsame)) {
                continue;
            }

            $dataEsame = \DateTimeImmutable::createFromMutable($esame->getDataEs());
            if (!$this->fPrenotazioneLezione->haLezioneInOrario($idIscritto, $dataEsame)) {
                $risultato[] = $iscritto;
            }
        }
        return $risultato;
    }

    //3)Metodo che prenota uno o più iscritti a un esame
    public function prenota(int $idDipendente, int $idEsame, array $idIscritti): array
    {
        if ($idDipendente <= 0) {
            throw new \InvalidArgumentException('Identificativo dipendente non valido.');
        }

        if ($idEsame <= 0) {
            throw new \InvalidArgumentException('Identificativo esame non valido.');
        }

        $idIscrittiValidi = array_values(array_unique(array_filter(array_map('intval', $idIscritti), fn($id) => $id > 0)));

        if ($idIscrittiValidi === []) {
            throw new \InvalidArgumentException('È necessario selezionare almeno un allievo valido.');
        }

        $dipendente = $this->fDipendente->getById($idDipendente);
        $esame = $this->fEsame->findById($idEsame);

        if (!$dipendente instanceof EDipendente) {
            throw new \InvalidArgumentException('Dipendente non trovato nel sistema.');
        }

        if (!$esame) {
            throw new \InvalidArgumentException('Sessione d\'esame non trovata.');
        }

        // Lo slot non deve essere già trascorso o in corso.
        if ($esame->getDataEs() <= new \DateTime()) {
            throw new \InvalidArgumentException('La sessione d\'esame selezionata è già iniziata o trascorsa.');
        }

        $prenotazioni = [];
        $totaliQuiz = $this->fQuiz->contaQuiz();
        $tipoEsame = $esame->getTipologia();
        $idoneitaService = $this->creaIdoneitaService();

        //Per ogni iscritto selezionato, verifica la disponibilità e l'idoneità prima di creare la prenotazione.
        foreach ($idIscrittiValidi as $id) {
            $iscritto = $this->fIscritto->findById((int) $id);

            if (!$iscritto) {
                throw new \InvalidArgumentException("Allievo con ID $id non trovato.");
            }

            if (!$iscritto->isStatoUtente()) {
                throw new \InvalidArgumentException("L'allievo {$iscritto->getNome()} {$iscritto->getCognome()} non ha un account attivo.");
            }

            $idIscritto = $iscritto->getId();

            // Verifica se ha già superato questa tipologia di esame
            if ($tipoEsame === TipologiaEsame::TEORIA && $this->fPrenotazioni->haSuperatoEsameTeorico($idIscritto)) {
                throw new \InvalidArgumentException("L'allievo {$iscritto->getNome()} {$iscritto->getCognome()} ha già superato l'esame di teoria.");
            }
            if ($tipoEsame === TipologiaEsame::PRATICA && $this->fPrenotazioni->haSuperatoEsamePratico($idIscritto)) {
                throw new \InvalidArgumentException("L'allievo {$iscritto->getNome()} {$iscritto->getCognome()} ha già superato l'esame pratico.");
            }

            // Verifica se è già prenotato alla stessa sessione
            if ($this->fPrenotazioni->isIscrittoGiaPrenotatoAdEsame($idIscritto, $idEsame)) {
                throw new \InvalidArgumentException("L'allievo {$iscritto->getNome()} {$iscritto->getCognome()} è già prenotato a questa sessione d'esame.");
            }

            // Verifica se ha già un'altra sessione futura attiva per la stessa tipologia
            if ($this->fPrenotazioni->haPrenotazioneFuturaAttivaPerTipologia($idIscritto, $tipoEsame)) {
                $descTipo = $tipoEsame === TipologiaEsame::TEORIA ? 'di teoria' : 'di guida pratica';
                throw new \InvalidArgumentException("L'allievo {$iscritto->getNome()} {$iscritto->getCognome()} ha già una sessione d'esame {$descTipo} futura prenotata.");
            }

            $dataEsame = \DateTimeImmutable::createFromMutable($esame->getDataEs());

            if ($this->fPrenotazioni->haEsameInOrario($idIscritto, $dataEsame)) {
                throw new \InvalidArgumentException(
                    "L'allievo {$iscritto->getNome()} {$iscritto->getCognome()} ha già un esame nello stesso orario."
                );
            }

            if ($this->fPrenotazioneLezione->haLezioneInOrario($idIscritto, $dataEsame)) {
                throw new \InvalidArgumentException(
                    "L'allievo {$iscritto->getNome()} {$iscritto->getCognome()} ha già una lezione prenotata nello stesso orario."
                );
            }

            if ($tipoEsame === TipologiaEsame::PRATICA &&
                !$this->fPrenotazioni->haSuperatoEsameTeorico($idIscritto)) {
                throw new \InvalidArgumentException('L\'allievo non è idoneo alla prova pratica.');
            }

            if (!$idoneitaService->isIdoneo($iscritto, $esame, $totaliQuiz)) {
                throw new \InvalidArgumentException(
                    "L'allievo {$iscritto->getNome()} {$iscritto->getCognome()} non possiede i requisiti di idoneità per questa prova."
                );
            }

            $prenotazione = new EPrenotazioneEsami();
            $prenotazione->init($dipendente, $esame, $iscritto, \CamassoMedelago\DriveMeSafely\Entity\StatoPrenotazioneEsame::PRENOTATO);
            $prenotazioni[] = $prenotazione;
        }

        return $prenotazioni;
    }

    /**
     * Annulla una prenotazione esame esistente in stato PRENOTATO prima dello svolgimento dell'esame.
     */
    public function annullaPrenotazione(int $idPrenotazione): void
    {
        if ($idPrenotazione <= 0) {
            throw new \InvalidArgumentException('Identificativo prenotazione non valido.');
        }

        $prenotazione = $this->fPrenotazioni->findById($idPrenotazione);
        if (!$prenotazione) {
            throw new \InvalidArgumentException('Prenotazione non trovata.');
        }

        if (!$prenotazione->getStato()->isAttiva()) {
            throw new \LogicException('Impossibile annullare una prenotazione che non è in stato attivo.');
        }

        $dataEsame = $prenotazione->getEsame()->getDataEs();
        if ($dataEsame <= new \DateTime()) {
            throw new \LogicException('Impossibile annullare la prenotazione di un esame già iniziato o passato.');
        }

        $prenotazione->setStato(\CamassoMedelago\DriveMeSafely\Entity\StatoPrenotazioneEsame::ANNULLATO);
        $this->fPrenotazioni->save($prenotazione);
    }

    //4)Metodo che conferma le prenotazioni effettuate
    public function conferma(array $prenotazioni): void
    {
        if ($prenotazioni === []) {
            throw new \InvalidArgumentException('Nessuna prenotazione da confermare.');
        }

        foreach ($prenotazioni as $p) {
            if (!$p instanceof EPrenotazioneEsami) {
                throw new \InvalidArgumentException('Elemento della prenotazione non valido.');
            }
        }

        $this->em->beginTransaction();
        try {
            foreach ($prenotazioni as $p) {
                $this->em->persist($p);
            }
            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $e) {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->rollback();
            }
            throw $e;
        }
    }

    /**
     * Esegue in maniera atomica prenotazione e salvataggio a database.
     *
     * @return EPrenotazioneEsami[]
     */
    public function prenotaEConferma(int $idDipendente, int $idEsame, array $idIscritti): array
    {
        $prenotazioni = $this->prenota($idDipendente, $idEsame, $idIscritti);
        $this->conferma($prenotazioni);
        return $prenotazioni;
    }

    private function creaIdoneitaService(): SIdoneitaEsame
    {
        return new SIdoneitaEsame(
            $this->em,
            $this->fIscritto,
            $this->fQuiz,
            $this->fSvolgimenti,
            $this->fEsame,
            $this->fPrenotazioni
        );
    }
}
