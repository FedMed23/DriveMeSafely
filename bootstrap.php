<?php
// bootstrap.php
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

// Carica automaticamente tutte le librerie installate con composer
require_once "vendor/autoload.php";

// ------------------- 1. Configurazione delle Entity -------------------
// Dove si trovano le classi entity
$paths = [__DIR__ . "/src/Entity"];

// Modalità di sviluppo (true = Doctrine mostrerà errori e log più dettagliati)
$isDevMode = true;

// ------------------- 2. Parametri di connessione al DB -------------------
$dbParams = [
    'driver'   => 'pdo_mysql',
    'user'     => 'root',       // default XAMPP
    'password' => '',           // default XAMPP
    'dbname'   => 'drive_me_safely', // nome del database creato in phpMyAdmin
];

// ------------------- 3. Creazione della configurazione Doctrine -------------------
$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);

// ------------------- 4. Creazione dell'Entity Manager -------------------
// L’Entity Manager gestisce tutte le operazioni sul DB (CRUD)
$entityManager = EntityManager::create($dbParams, $config);
?>