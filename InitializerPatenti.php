<?php

use CamassoMedelago\DriveMeSafely\Entity\EPatente;
use CamassoMedelago\DriveMeSafely\Entity\ESpesa;
use Doctrine\ORM\EntityManagerInterface;

require_once __DIR__ . '/vendor/autoload.php';

/** @var EntityManagerInterface $em */
$em = require __DIR__ . '/bootstrap.php';
$connection = $em->getConnection();

$spese = [
    'Marca da bollo dello Stato' => [15.00, 'PATENTE'],
    'Visita Medica Idoneità' => [45.00, 'PATENTE'],
    'Tassa Motorizzazione Esame Teorico' => [25.00, 'PATENTE'],
    'Tassa Motorizzazione Esame Pratico' => [35.00, 'PATENTE'],
    'Guide base A' => [200.00, 'PATENTE'],
    'Guide base B' => [300.00, 'PATENTE'],
    'Guide base C' => [400.00, 'PATENTE'],
    'Guide base D' => [500.00, 'PATENTE'],
];

$patenti = [
    'A' => [
        'Corso completo teorico e pratico per motocicli e scooter.',
        [
            'Marca da bollo dello Stato',
            'Visita Medica Idoneità',
            'Tassa Motorizzazione Esame Teorico',
            'Tassa Motorizzazione Esame Pratico',
            'Guide base A',
        ],
    ],
    'B' => [
        'Corso teorico e pratico per autoveicoli.',
        [
            'Marca da bollo dello Stato',
            'Visita Medica Idoneità',
            'Tassa Motorizzazione Esame Teorico',
            'Tassa Motorizzazione Esame Pratico',
            'Guide base B',
        ],
    ],
    'C' => [
        'Corso completo teorico e pratico per autoveicoli per trasporto di cose.',
        [
            'Marca da bollo dello Stato',
            'Visita Medica Idoneità',
            'Tassa Motorizzazione Esame Teorico',
            'Tassa Motorizzazione Esame Pratico',
            'Guide base C',
        ],
    ],
    'D' => [
        'Corso completo teorico e pratico per autoveicoli per il trasporto di persone.',
        [
            'Marca da bollo dello Stato',
            'Visita Medica Idoneità',
            'Tassa Motorizzazione Esame Teorico',
            'Tassa Motorizzazione Esame Pratico',
            'Guide base D',
        ],
    ],
];

$em->beginTransaction();

try {
    $spesePersistite = [];
    $spesaRepository = $em->getRepository(ESpesa::class);

    foreach ($spese as $tipologia => [$importo, $ambito]) {
        $spesa = $spesaRepository->findOneBy([
            'tipologia' => $tipologia,
            'ambito' => $ambito,
        ]);

        if ($spesa === null) {
            $spesa = new ESpesa($tipologia, $importo, $ambito);
            $em->persist($spesa);
        }

        $spesePersistite[$tipologia] = $spesa;
    }

    $em->flush();

    $patenteRepository = $em->getRepository(EPatente::class);

    foreach ($patenti as $tipo => [$descrizione, $tipologieSpese]) {
        $patente = $patenteRepository->findOneBy(['tipo' => $tipo]);

        if ($patente === null) {
            $patente = new EPatente($tipo, $descrizione);
            $em->persist($patente);
            $em->flush();
        }

        foreach ($tipologieSpese as $tipologia) {
            $connection->executeStatement(
                'INSERT IGNORE INTO patente_has_spesa (id_patente, id_spesa)
                 VALUES (:id_patente, :id_spesa)',
                [
                    'id_patente' => $patente->getId(),
                    'id_spesa' => $spesePersistite[$tipologia]->getIdSpesa(),
                ]
            );
        }
    }

    $em->commit();
    echo "Patenti e spese inizializzate correttamente." . PHP_EOL;
} catch (\Throwable $exception) {
    if ($em->getConnection()->isTransactionActive()) {
        $em->rollback();
    }

    throw $exception;
}
