<?php

namespace CamassoMedelago\DriveMeSafely\View;

use CamassoMedelago\DriveMeSafely\Smarty\StartSmarty;

class VMieiEsami
{
    public function showStorico(array $prenotazioni, array $effettuazioniPerPrenotazione, array $eventiCalendario): void
    {
        $smarty = StartSmarty::configuration();
        $smarty->assign('storicoEsami', $prenotazioni);
        $smarty->assign('effettuazioniPerPrenotazione', $effettuazioniPerPrenotazione);
        $smarty->assign('eventiCalendario', json_encode(
            $eventiCalendario,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
        ));
        $smarty->display('miei_esami.tpl');
    }

    public function showError(string $message, int $code): void
    {
        http_response_code($code);
        echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    }
}
