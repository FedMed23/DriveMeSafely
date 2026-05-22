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
            case 'inserisciDati':
            
                $idPa = $_POST['idPa'];
            
                $smarty->assign('idPa', $idPa);
            
                $smarty->display('iscrizione/VInserisciDati.tpl');
            
            break;
            case 'confermaIscrizione':
            
                $controller = new CIscrizione($fIscritto, $fPatente);
            
                $iscritto = $controller->inserisciDati([
                    'nome' => $_POST['nome'],
                    'cognome' => $_POST['cognome'],
                    'email' => $_POST['email'],
                    'username' => $_POST['username'],
                    'password' => $_POST['password'],
                    'stato' => true,
                    'codiceFiscale' => $_POST['codiceFiscale'],
                    'dataNascita' => $_POST['dataNascita'],
                    'luogoNascita' => $_POST['luogoNascita'],
                    'indirizzo' => $_POST['indirizzo'],
                    'numeroTelefono' => $_POST['numeroTelefono'],
                    'tipoPatente' => null
                ]);
            
                $controller->confermaDati(
                    $_POST['idPa'],
                    $iscritto
                );
            
                $smarty->assign('iscritto', $iscritto);
            
                $smarty->display(
                    'iscrizione/VConfermaIscrizione.tpl'
                );
            
            break;
}
