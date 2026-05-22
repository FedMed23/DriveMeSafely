<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use CamassoMedelago\DriveMeSafely\Controller\CIscrizione;
use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FPatente;

// Doctrine (già configurato da voi)
$entityManager = require __DIR__ . '/../bootstrap.php';

// Foundation
$fIscritto = new FIscritto($entityManager);
$fPatente  = new FPatente($entityManager);

// Controller
$controller = new CIscrizione($fIscritto, $fPatente);

// 🔥 dati presi dal controller
$patenti = $controller->getPatenti();


// 👉 qui colleghi la VIEW
include __DIR__ . '/../src/View/Iscrizione/VPatenti.php';