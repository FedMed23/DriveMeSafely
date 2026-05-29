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

        return $smarty;
    }
}
