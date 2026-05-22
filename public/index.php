<?php

require_once __DIR__ . '/../vendor/autoload.php';

use CamassoMedelago\DriveMeSafely\Controller\CIscrizione;

$smarty = new Smarty\Smarty();

$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');

$page = $_GET['page'] ?? 'home';

switch($page) {

    case 'iscrizione':

        $controller = new CIscrizione($fIscritto, $fPatente);

        $patenti = $controller->getPatenti();

        $smarty->assign('patenti', $patenti);

        $smarty->display('iscrizione/VPatenti.tpl');

        break;
}
