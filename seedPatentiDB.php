<?php

use CamassoMedelago\DriveMeSafely\Entity\EPatente;

require_once __DIR__ . "/vendor/autoload.php";

$entityManager = require __DIR__ . "/bootstrap.php";

// elenco patenti (solo tipo)
$patenti = [
    "AM",
    "A1",
    "A2",
    "A",
    "B",
    "C",
    "D"
];

/*foreach ($patenti as $tipo) {

    $esistente = $entityManager->getRepository(EPatente::class)
        ->getByTipo(["tipoPatente" => $tipo]);

    if ($esistente) {
        echo "Patente $tipo già presente\n";
        continue;
    }

    $patente = new EPatente($tipo);

    $entityManager->persist($patente);

    echo "Inserita patente $tipo\n";
}
*/

$entityManager->flush();

echo "SEED COMPLETATO 🚀\n";