<?php

namespace CamassoMedelago\DriveMeSafely\View;

use CamassoMedelago\DriveMeSafely\Entity\EProprietario;
use CamassoMedelago\DriveMeSafely\Smarty\StartSmarty;

class VProprietario
{
    public function show(EProprietario $utente): void
    {
        $smarty = StartSmarty::configuration();
        $smarty->assign('utenteLoggato', $utente);
        $smarty->display('dashboard_proprietario.tpl');
    }
}
