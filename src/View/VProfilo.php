<?php

namespace CamassoMedelago\DriveMeSafely\View;

use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Smarty\StartSmarty;

class VProfilo
{
    public function showProfilo(EIscritto $iscritto): void
    {
        $smarty = StartSmarty::configuration();
        $smarty->assign('iscritto', $iscritto);
        $smarty->display('profilo.tpl');
    }

    public function showError(string $message, int $code): void
    {
        http_response_code($code);
        echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    }
}
