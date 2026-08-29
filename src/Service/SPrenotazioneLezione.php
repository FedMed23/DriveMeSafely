<?php

namespace CamassoMedelago\DriveMeSafely\Service;

use CamassoMedelago\DriveMeSafely\Entity\ELezioneTeoria;
use CamassoMedelago\DriveMeSafely\Entity\EPrenotazioneLezione;
use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FLezione;
use CamassoMedelago\DriveMeSafely\Foundation\FPrenotazioneLezione;
use Doctrine\ORM\EntityManagerInterface;

class SPrenotazioneLezione
{
    private FLezione $fLezione;
    private FIscritto $fIscritto;
    private FPrenotazioneLezione $fPrenotazione;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->fLezione = new FLezione($em);
        $this->fIscritto = new FIscritto($em);
        $this->fPrenotazione = new FPrenotazioneLezione($em);
    }

    public function getCalendarioAllievo(int $idIscritto): array
    {
        return [
            'storicoPrenotazioni' => $this->fPrenotazione->findByIscrittoId($idIscritto),
            'lezioniDisponibili' => $this->fPrenotazione->findLezioniDisponibili($idIscritto),
        ];
    }

    public function prenota(int $idIscritto, int $idLezione): EPrenotazioneLezione
    {
        $iscritto = $this->fIscritto->findById($idIscritto);
        if ($iscritto === null) {
            throw new \InvalidArgumentException('Allievo non trovato nel sistema.');
        }

        $lezione = $this->fLezione->findById($idLezione);
        if ($lezione === null || $lezione->getDataOra() <= new \DateTimeImmutable()) {
            throw new \InvalidArgumentException('Lo slot selezionato non è più disponibile.');
        }
        if ($this->fPrenotazione->haLezioneInOrario($idIscritto, $lezione->getDataOra())) {
            throw new \InvalidArgumentException('Hai già una lezione prenotata in questo stesso orario.');
        }
        if ($lezione instanceof ELezioneTeoria
            && $this->fPrenotazione->isAulaPiena($idLezione, 30)
        ) {
            throw new \InvalidArgumentException('L’aula ha raggiunto la capienza massima.');
        }

        return EPrenotazioneLezione::crea($iscritto, $lezione);
    }

    public function prenotaGuida(int $idIscritto, int $idLezione): EPrenotazioneLezione
    {
        $prenotazione = $this->prenota($idIscritto, $idLezione);
        if ($prenotazione->getLezione() instanceof ELezioneTeoria) {
            throw new \InvalidArgumentException('Lo slot selezionato non corrisponde a una guida pratica.');
        }
        return $prenotazione;
    }

    public function prenotaTeoria(int $idIscritto, int $idLezione): EPrenotazioneLezione
    {
        $prenotazione = $this->prenota($idIscritto, $idLezione);
        if (!$prenotazione->getLezione() instanceof ELezioneTeoria) {
            throw new \InvalidArgumentException('Lo slot selezionato non corrisponde a una lezione teorica.');
        }
        return $prenotazione;
    }

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

    public function getPalinsesto(): array
    {
        return $this->fLezione->findAllPalinsesto();
    }

    public function getPrenotazioniLezione(int $idLezione): array
    {
        return $this->fPrenotazione->findByLezioneId($idLezione);
    }
}
