<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/../vendor/autoload.php';

use CamassoMedelago\DriveMeSafely\Controller\CIscrizione;
use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FPatente;
use Smarty\Smarty;
use \DateTimeImmutable;

// Inizializza Doctrine (configurazione esterna)
$entityManager = require __DIR__ . "/../bootstrap.php";

// Inizializza Smarty
$smarty = new Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates/');
$smarty->setCompileDir(__DIR__ . '/../templates_c/');
$smarty->setCacheDir(__DIR__ . '/../cache/');
$smarty->setConfigDir(__DIR__ . '/../configs/');

// Inizializza le fondazioni
$fIscritto = new FIscritto($entityManager);
$fPatente = new FPatente($entityManager);

// Inizializza il controller
$cIscrizione = new CIscrizione($fIscritto, $fPatente);

// Leggi il parametro 'page' per il routing
$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'iscrizione':
        // Mostra la lista delle patenti
        $patenti = $cIscrizione->getPatenti();
        $smarty->assign('patenti', $patenti);
        $smarty->display('iscrizione/VPatenti.tpl');
        break;

   case 'inserisciDati':
            echo "<pre>";
        
            echo "REQUEST METHOD:\n";
            var_dump($_SERVER['REQUEST_METHOD']);
        
            echo "\nPOST:\n";
            var_dump($_POST);
        
            echo "</pre>";
        
            die();
        break;
    
    case 'confermaIscrizione':

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        
        $patente = $fPatente->getPatenteById($_POST['idPa']);

        $iscritto = $cIscrizione->inserisciDati([
                'nome' => $_POST['nome'],
                'cognome' => $_POST['cognome'],
                'email' => $_POST['email'],
                'username' => $_POST['username'],
                'password' => $_POST['password'],
                'stato' => true,
                'codiceFiscale' => $_POST['codiceFiscale'],
                'dataNascita' => new DateTimeImmutable($_POST['dataNascita']), 
                'luogoNascita' => $_POST['luogoNascita'],
                'indirizzo' => $_POST['indirizzo'],
                'numeroTelefono' => $_POST['numeroTelefono'],
                'tipoPatente' => $patente
        ]);

        $cIscrizione->confermaDati($_POST['idPa'], $iscritto);

        $smarty->assign('iscritto', $iscritto);
        $smarty->display('iscrizione/VConfermaIscrizione.tpl');

    } else {
        header("Location: index.php?page=iscrizione");
        exit;
    }

    break;

    case 'home':
    default:
        // Pagina home generica
        $smarty->display('home.tpl');
        break;
    

}

