<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Foundation\FEsame;
use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FPrenotazioneEsami;

use CamassoMedelago\DriveMeSafely\Entity\EEsame;
use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Entity\EPrenotazioneEsami;
use CamassoMedelago\DriveMeSafely\Entity\EDipendente;

use DateTimeImmutable;

class CGestioneEsami
{
    private FEsame $fEsame;
    private FIscritto $fIscritto;
    private FPrenotazioneEsami $fPrenotazioneEsami;

    public function __construct(
        FEsame $fEsame,
        FIscritto $fIscritto,
        FPrenotazioneEsami $fPrenotazioneEsami
    ) {
        $this->fEsame = $fEsame;
        $this->fIscritto = $fIscritto;
        $this->fPrenotazioneEsami = $fPrenotazioneEsami;
    }

    // Visualizza form di ricerca esami
    public function mostraFormRicerca(): array
    {
        return [
            'tipologia' => '',
            'data' => ''
        ];
    }

    // Ricerca disponibilità esami
    public function cercaDisponibilita(
        string $tipologia
    ): array {

        return $this->fEsame->getEsamiByTipologia($tipologia);
    }

    // Visualizza candidati idonei
    public function getCandidatiIdonei(): array
    {
        return $this->fIscritto->getAllIscritti();
    }

    // Prenotazione esame
    public function prenotaEsame(
        EDipendente $dipendente,
        int $idEsame,
        array $candidati
    ): array {

        // Recupero esame
        $esame = $this->fEsame->getEsameById($idEsame);

        if (!$esame) {
            throw new \Exception("Esame non trovato");
        }

        $prenotazioni = [];

        // Prenotazione per ogni candidato selezionato
        foreach ($candidati as $idIscritto) {

            $iscritto = $this->fIscritto
                ->getIscrittoById($idIscritto);

            if ($iscritto) {

                $prenotazione = new EPrenotazioneEsami(
                    $dipendente,
                    $esame,
                    new DateTimeImmutable(),
                    "confermato"
                );

                $this->fPrenotazioneEsami
                    ->save($prenotazione);

                $prenotazioni[] = $prenotazione;
            }
        }

        return $prenotazioni;
    }

    // Conferma prenotazione
    public function confermaPrenotazione(
        EEsame $esame
    ): string {

        return "Prenotazione completata con successo "
            . "per l'esame "
            . $esame->getTipologia()
            . " del giorno "
            . $esame->getDataEsame()->format('d/m/Y');
    }
}
?>
