<?php

namespace CamassoMedelago\DriveMeSafely\View;

use CamassoMedelago\DriveMeSafely\Entity\EDipendente;
use CamassoMedelago\DriveMeSafely\Smarty\StartSmarty;

class VSegreteria
{
    public function show(EDipendente $utente): void
    {
        $smarty = StartSmarty::configuration();
        $smarty->assign('utenteLoggato', $utente);
        $smarty->display('segreteria.tpl');
    }
}
