<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Foundation\FGuida;
use CamassoMedelago\DriveMeSafely\Entity\EGuida;

class CCancellazioneGuide
{
    private FGuida $fGuida;

    public function __construct(FGuida $fGuida)
    {
        $this->fGuida = $fGuida;
    }

    // Visualizza elenco guide prenotate
    public function getGuide(): array
    {
        return $this->fGuida->getAllGuide();
    }

    // Cancellazione di una guida
    public function cancellaGuida(int $idGuida): void
    {
        // Recupero guida
        $guida = $this->fGuida->getGuidaById($idGuida);

        if (!$guida) {
            throw new \Exception("Guida non trovata");
        }

        // Eliminazione guida
        $this->fGuida->delete($guida);
    }

    // Conferma cancellazione
    public function confermaCancellazione(EGuida $guida): string
    {
        return "La prenotazione della guida del giorno "
            . $guida->getDataOra()->format('d/m/Y H:i')
            . " è stata cancellata con successo";
    }
}
?>
