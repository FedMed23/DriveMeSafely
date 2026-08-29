<?php
// 1. Carica l'autoloader di Composer
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

// 2. Carica Smarty per la gestione dei template visivi
require_once __DIR__ . '/../Smarty/StartSmarty.php';

// 3. Carica il bootstrap di Doctrine per creare e ottenere l'EntityManager ($em)
require_once __DIR__ . '/../bootstrap.php'; 

use CamassoMedelago\DriveMeSafely\Controller\CFrontController;

// 4. Ora $em esiste, è configurato e può essere passato in totale sicurezza al Front Controller!
$fcontroller = new CFrontController($em);
$fcontroller->run($_SERVER['REQUEST_URI']);
