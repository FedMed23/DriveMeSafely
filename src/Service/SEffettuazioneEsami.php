<?php

namespace CamassoMedelago\DriveMeSafely\Service;

use CamassoMedelago\DriveMeSafely\Entity\EEffettuazioneEsami;
use CamassoMedelago\DriveMeSafely\Entity\StatoPrenotazioneEsame;
use CamassoMedelago\DriveMeSafely\Foundation\FEffettuazioneEsami;
use CamassoMedelago\DriveMeSafely\Foundation\FPrenotazioneEsami;
use Doctrine\ORM\EntityManagerInterface;

//Service che implementa la logica per registrare e modificare l'esito (effettuazione) degli esami da parte della segreteria
class SEffettuazioneEsami
{
    private FEffettuazioneEsami $fEffettuazione;
    private FPrenotazioneEsami $fPrenotazioni;

    public function __construct(private EntityManagerInterface $em)
    {
        $this->fEffettuazione = new FEffettuazioneEsami($em);
        $this->fPrenotazioni = new FPrenotazioneEsami($em);
    }

    //1)Metodo che registra l'esito di un esame già svolto, a partire da una prenotazione attiva
    public function registraEsito(int $idPrenotazione, int $tentativi, bool $superato): EEffettuazioneEsami
    {
        if ($idPrenotazione <= 0) {
            throw new \InvalidArgumentException('Identificativo prenotazione non valido.');
        }
        if ($tentativi < 1) {
            throw new \InvalidArgumentException('Il numero di tentativi deve essere almeno 1.');
        }

        $prenotazione = $this->fPrenotazioni->findById($idPrenotazione);
        if (!$prenotazione) {
            throw new \InvalidArgumentException('Prenotazione non trovata.');
        }
        if (!$prenotazione->getStato()->isAttiva()) {
            throw new \LogicException('Impossibile registrare un esito: la prenotazione non è in stato attivo (PRENOTATO).');
        }
        if ($prenotazione->getEsame()->getDataEs() > new \DateTime()) {
            throw new \LogicException('Non è possibile registrare l’esito di un esame non ancora svolto.');
        }
        if ($this->fEffettuazione->getByPrenotazioneEsame($prenotazione) !== []) {
            throw new \LogicException('Per questa prenotazione è già stato registrato un esito. Usa la modifica per correggerlo.');
        }

        $this->em->beginTransaction();
        try {
            $effettuazione = new EEffettuazioneEsami($prenotazione, $tentativi, $superato);
            $this->fEffettuazione->save($effettuazione);

            // Coerenza: una volta registrato l'esito, la prenotazione risulta effettuata
            $prenotazione->setStato(StatoPrenotazioneEsame::EFFETTUATO);
            $this->fPrenotazioni->save($prenotazione);

            $this->em->commit();
            return $effettuazione;
        } catch (\Throwable $e) {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->rollback();
            }
            throw $e;
        }
    }

    //2)Metodo che modifica l'esito già registrato (es. correzione di un errore di inserimento della segreteria)
    public function modificaEsito(int $idEffettuazione, int $tentativi, bool $superato): void
    {
        if ($idEffettuazione <= 0) {
            throw new \InvalidArgumentException('Identificativo effettuazione non valido.');
        }
        if ($tentativi < 1) {
            throw new \InvalidArgumentException('Il numero di tentativi deve essere almeno 1.');
        }

        $effettuazione = $this->fEffettuazione->getEffettuazioneById($idEffettuazione);
        if (!$effettuazione) {
            throw new \InvalidArgumentException('Effettuazione non trovata.');
        }

        $prenotazione = $effettuazione->getPrenotazioneEsame();
        $idIscritto = $prenotazione->getAllievo()->getId();
        $tipologia = $prenotazione->getEsame()->getTipologia();
        if ($this->fPrenotazioni->haPrenotazioneFuturaAttivaPerTipologia($idIscritto, $tipologia)) {
            throw new \LogicException('Impossibile modificare l’esito: l’allievo ha già una nuova prenotazione attiva per questa tipologia di esame.');
        }

        $effettuazione->setTentativi($tentativi);
        $effettuazione->setSuperato($superato);
        $this->fEffettuazione->update($effettuazione);
    }

    //3)Metodo che annulla una registrazione di esito e ripristina la prenotazione a PRENOTATO
    // (utile in caso di errore, ad es. l'esito è stato registrato per la prenotazione sbagliata)
    public function annullaEsito(int $idEffettuazione): void
    {
        if ($idEffettuazione <= 0) {
            throw new \InvalidArgumentException('Identificativo effettuazione non valido.');
        }

        $effettuazione = $this->fEffettuazione->getEffettuazioneById($idEffettuazione);
        if (!$effettuazione) {
            throw new \InvalidArgumentException('Effettuazione non trovata.');
        }

        $prenotazione = $effettuazione->getPrenotazioneEsame();

        $this->em->beginTransaction();
        try {
            $this->fEffettuazione->delete($effettuazione);

            $prenotazione->setStato(StatoPrenotazioneEsame::PRENOTATO);
            $this->fPrenotazioni->save($prenotazione);

            $this->em->commit();
        } catch (\Throwable $e) {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->rollback();
            }
            throw $e;
        }
    }

    //Metodo che riporta tutte le effettuazioni collegate a una prenotazione (in genere al più una)
    public function getByPrenotazione(int $idPrenotazione): array
    {
        $prenotazione = $this->fPrenotazioni->findById($idPrenotazione);
        if (!$prenotazione) {
            return [];
        }
        return $this->fEffettuazione->getByPrenotazioneEsame($prenotazione);
    }

    /**
     * Riporta la mappa [idPrenotazioneEsame => EEffettuazioneEsami] per tutte le effettuazioni
     * registrate, utile alla vista per abbinare ogni riga dello storico prenotazioni al proprio esito.
     */
    public function getMappaPerPrenotazione(): array
    {
        $mappa = [];
        foreach ($this->fEffettuazione->getAllEffettuazioni() as $effettuazione) {
            $mappa[$effettuazione->getPrenotazioneEsame()->getIdPrenotazioneEsame()] = $effettuazione;
        }
        return $mappa;
    }

    /**
     * Riporta gli ID delle prenotazioni (tra quelle passate) il cui esame è già stato svolto
     * (data della sessione d'esame nel passato), utile alla vista per decidere se mostrare
     * il pulsante "Registra esito" (esame svolto, nessun esito) invece che solo "Annulla prenotazione".
     *
     * @param array<\CamassoMedelago\DriveMeSafely\Entity\EPrenotazioneEsami> $prenotazioni
     * @return int[]
     */
    public function getIdPrenotazioniConEsameSvolto(array $prenotazioni): array
    {
        $adesso = new \DateTime();
        return array_values(array_map(
            static fn($p) => $p->getIdPrenotazioneEsame(),
            array_filter($prenotazioni, static fn($p) => $p->getEsame()->getDataEs() <= $adesso)
        ));
    }
}
