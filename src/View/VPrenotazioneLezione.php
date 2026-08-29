<?php

namespace CamassoMedelago\DriveMeSafely\View;

use CamassoMedelago\DriveMeSafely\Smarty\StartSmarty;

class VPrenotazioneLezione
{
    public function showCalendario(array $storico, array $disponibili, bool $successo, ?string $errore): void
    {
        $smarty = StartSmarty::configuration();
        $smarty->assign('storicoPrenotazioni', $storico);
        $smarty->assign('lezioniDisponibili', $disponibili);
        $smarty->assign('successo', $successo);
        $smarty->assign('errore', $errore);
        $smarty->display('mie_prenotazioni.tpl');
    }

    public function showError(string $message, int $code): void
    {
        http_response_code($code);
        echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    }
}
