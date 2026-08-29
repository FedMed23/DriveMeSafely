<?php

namespace CamassoMedelago\DriveMeSafely\View;

use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Entity\EProprietario;
use CamassoMedelago\DriveMeSafely\Smarty\StartSmarty;

class VMieSpese
{
    public function showSpese(
        array $report,
        object $utente,
        bool $successo = false,
        ?string $errore = null
    ): void {
        $smarty = StartSmarty::configuration();
        $smarty->assign('report', $report);
        $smarty->assign('utente', $utente);
        $smarty->assign('successo', $successo);
        $smarty->assign('errore', $errore);
        $smarty->assign(
            'homeUrl',
            $utente instanceof EProprietario ? $smarty->getTemplateVars('request')['contextPath'] . '/home/proprietario' : $smarty->getTemplateVars('request')['contextPath'] . '/home'
        );
        $smarty->display('mie_spese.tpl');
    }

    public function showError(string $message, int $code): void
    {
        http_response_code($code);
        echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    }
}
