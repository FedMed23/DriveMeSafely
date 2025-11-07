<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;

// Includi il bootstrap che crea l'EntityManager
require_once __DIR__ . '/bootstrap.php';

// Ottieni l'EntityManager (deve essere ritornato o definito nel bootstrap)
return ConsoleRunner::createHelperSet($entityManager);
?>