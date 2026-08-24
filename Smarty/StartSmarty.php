<?php

namespace CamassoMedelago\DriveMeSafely\Smarty;

use Smarty\Smarty;

class StartSmarty
{
    public static function configuration(): Smarty
    {
        $smarty = new Smarty();

        $smarty->setTemplateDir(__DIR__ . '/../templates/');
        $smarty->setCompileDir(__DIR__ . '/../templates_c/');
        $smarty->setCacheDir(__DIR__ . '/../cache/');
        $smarty->setConfigDir(__DIR__ . '/../configs/');
        
        // Calcola dinamicamente il context path (es: /DriveMeSafely/public)
        $contextPath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        
        // Assegna l'array 'request' per far funzionare i tuoi template esistenti!
        $smarty->assign('request', ['contextPath' => $contextPath ]);
        
        return $smarty;
    }
}
