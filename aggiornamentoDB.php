<?php

require_once __DIR__ . "/vendor/autoload.php";

$entityManager = require __DIR__ . "/bootstrap.php";

use Doctrine\ORM\Tools\SchemaTool;

$tool = new SchemaTool($entityManager);

// prende tutte le entity mappate
$classes = $entityManager->getMetadataFactory()->getAllMetadata();
echo count($classes);

foreach ($classes as $c) {
    echo $c->getName() . PHP_EOL;
}

//Aggiona db cancellando tutto
$tool->dropSchema($classes);
$tool->createSchema($classes);

echo "DB aggiornato con successo!";
