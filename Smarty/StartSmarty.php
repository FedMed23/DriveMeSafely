<?php

namespace CamassoMedelago\DriveMeSafely\Smarty;

use Smarty\Smarty;

class StartSmarty
{
    public static function configuration(): Smarty
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $smarty = new Smarty();

        $smarty->setTemplateDir(__DIR__ . '/../templates/');
        $smarty->setCompileDir(__DIR__ . '/../templates_c/');
        $smarty->setCacheDir(__DIR__ . '/../cache/');
        $smarty->setConfigDir(__DIR__ . '/../configs/');

        $contextPath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $homeUrl = $_SESSION['homeUrl'] ?? $contextPath . '/home';

        $smarty->assign('request', ['contextPath' => $contextPath]);
        $smarty->assign('homeUrl', $homeUrl);

        return $smarty;
    }
}
