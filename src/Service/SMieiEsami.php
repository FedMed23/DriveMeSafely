<?php

namespace CamassoMedelago\DriveMeSafely\Service;

use CamassoMedelago\DriveMeSafely\Entity\TipologiaEsame;
use CamassoMedelago\DriveMeSafely\Foundation\FPrenotazioneEsami;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service che implementa la logica per la consultazione, da parte dell'utente
 * registrato, delle proprie prenotazioni d'esame e dei relativi esiti.
 * Sola lettura: la prenotazione/annullamento/registrazione esito restano
 * di competenza esclusiva della segreteria (SPrenotazioneEsame/SEffettuazioneEsami).
 */
class SMieiEsami
{
    private FPrenotazioneEsami $fPrenotazioni;
    private SEffettuazioneEsami $sEffettuazione;

    public function __construct(EntityManagerInterface $em)
    {
        $this->fPrenotazioni = new FPrenotazioneEsami($em);
        $this->sEffettuazione = new SEffettuazioneEsami($em);
    }

    /**
     * Riporta lo storico delle prenotazioni d'esame dell'iscritto, per ciascuna
     * l'eventuale esito (effettuazione) già registrato dalla segreteria, e gli
     * eventi calendario delle proprie sessioni d'esame prenotate.
     */
    public function getStoricoEsami(int $idIscritto): array
    {
        $prenotazioni = $this->fPrenotazioni->findByIscritto($idIscritto);

        return [
            'prenotazioni' => $prenotazioni,
            'effettuazioniPerPrenotazione' => $this->sEffettuazione->getMappaPerPrenotazione(),
            'eventiCalendario' => $this->costruisciEventiCalendario($prenotazioni),
        ];
    }

    /**
     * Costruisce gli eventi calendario (formato FullCalendar) a partire dalle
     * sessioni d'esame prenotate dallo studente.
     */
    private function costruisciEventiCalendario(array $prenotazioni): array
    {
        return array_map(static function ($p): array {
            $esame = $p->getEsame();
            $pratica = $esame->getTipologia() === TipologiaEsame::PRATICA;
            return [
                'title' => $pratica ? 'Esame pratico' : 'Esame teoria',
                'start' => $esame->getDataEs()->format(DATE_ATOM),
                'color' => $pratica ? '#2b6cb0' : '#6b46c1',
            ];
        }, $prenotazioni);
    }
}
