<?php
namespace CamassoMedelago\DriveMeSafely\View;
use CamassoMedelago\DriveMeSafely\Smarty\StartSmarty;
class VHome
{
    public function showHome($utenteLoggato = null): void
    {
        $smarty = StartSmarty::configuration();
        $smarty->assign('titolo', 'DriveMeSafely');
        if ($utenteLoggato !== null) {
            $smarty->assign('utenteLoggato', $utenteLoggato);
        }
        $smarty->display('home.tpl');
    }
}