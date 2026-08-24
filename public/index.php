<?php
// 1. Carica l'autoloader di Composer
require_once __DIR__ . '/../vendor/autoload.php';

// 2. Carica Smarty per la gestione dei template visivi
require_once __DIR__ . '/../Smarty/StartSmarty.php';

// 3. Carica il bootstrap di Doctrine per creare e ottenere l'EntityManager ($em)
require_once __DIR__ . '/../bootstrap.php'; 

use CamassoMedelago\DriveMeSafely\Controller\CFrontController;

// 4. Ora $em esiste, è configurato e può essere passato in totale sicurezza al Front Controller!
$fcontroller = new CFrontController($em);
$fcontroller->run($_SERVER['REQUEST_URI']);
