
<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FGuida;
use CamassoMedelago\DriveMeSafely\Entity\EGuida;
use DateTimeImmutable;

class CPrenotazioneGuide
{
    private FIscritto $fIscritto;
    private FGuida $fGuida;

    public function __construct(FIscritto $fIscritto, FGuida $fGuida)
    {
        $this->fIscritto = $fIscritto;
        $this->fGuida = $fGuida;
    } 

  // Visualizza elenco guide
    public function getGuide(): array
    {
        return $this->fGuida->getAllGuide();
    }
   // Restituisce il form di prenotazione
    public function mostraFormPrenotazione(): array
    {
        return [
            'numeroGuida' => '',
            'dataOra' => '',
            'idIscritto' => ''
        ];
    }

    // Prenotazione nuova guida
    public function prenotaGuida(array $datiGuida): EGuida
    {
        // Recupero iscritto
        $iscritto = $this->fIscritto->getIscrittoById(
            $datiGuida['idIscritto']
        );

        if (!$iscritto) {
            throw new \Exception("Iscritto non trovato");
        }

        // Creazione nuova guida
        $guida = new EGuida(
            $iscritto,
            $datiGuida['numeroGuida'],
            new DateTimeImmutable($datiGuida['dataOra'])
        );

        // Salvataggio guida
        $this->fGuida->save($guida);

        return $guida;
    }

    // Conferma prenotazione
    public function confermaPrenotazione(EGuida $guida): string
    {
        return "Prenotazione della guida confermata con successo per il giorno "
            . $guida->getDataOra()->format('d/m/Y H:i');
    }
}
?>
