<?php
// bootstrap.php
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Cache\ArrayCache;

// Carica automaticamente tutte le librerie installate con composer
require_once __DIR__ . "/vendor/autoload.php";

// ------------------- 1. Configurazione delle Entity -------------------
// Dove si trovano le classi entity
$paths = [__DIR__ . "/src/Entity"];

// Modalità di sviluppo (true = Doctrine mostrerà errori e log più dettagliati)
$isDevMode = true;

// ------------------- 2. Configurazione della cache -------------------
$cache = new ArrayCache(); // cache in memoria Doctrine

// ------------------- 3. Parametri di connessione al DB -------------------
$dbParams = [
    'driver'   => 'pdo_mysql',
    'host'     => '127.0.0.1', 
    'user'     => 'root',       // default XAMPP
    'password' => '',           // default XAMPP
    'dbname'   => 'drivemesafely_php_db', // nome del database creato in phpMyAdmin
];

// ------------------- 3. Creazione della configurazione Doctrine -------------------
$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode,  null, $cache, false);


// ------------------- 4. Creazione dell'Entity Manager -------------------
// L’Entity Manager gestisce tutte le operazioni sul DB (CRUD)
$em = EntityManager::create($dbParams, $config);

return $em;
?>
