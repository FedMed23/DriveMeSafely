<?php

use CamassoMedelago\DriveMeSafely\Entity\EDipendente;
use CamassoMedelago\DriveMeSafely\Entity\EProprietario;

require_once __DIR__ . '/vendor/autoload.php';

/** @var \Doctrine\ORM\EntityManagerInterface $em */
$em = require __DIR__ . '/bootstrap.php';
$utenti = [
    [
        'classe' => EDipendente::class,
        'username' => 'segreteria.demo',
        'email' => 'segreteria.demo@drivemesafely.test',
        'nome' => 'Demo',
        'cognome' => 'Segreteria',
        'password' => 'SegreteriaDemo2026!',
    ],
    [
        'classe' => EProprietario::class,
        'username' => 'proprietario.demo',
        'email' => 'proprietario.demo@drivemesafely.test',
        'nome' => 'Demo',
        'cognome' => 'Proprietario',
        'password' => 'ProprietarioDemo2026!',
    ],
];

$em->beginTransaction();
try {
    foreach ($utenti as $dati) {
        $repository = $em->getRepository($dati['classe']);
        if ($repository->findOneBy(['username' => $dati['username']])) {
            continue;
        }
        if ($dati['classe'] === EDipendente::class) {
            $utente = new EDipendente(
                $dati['nome'], $dati['cognome'], $dati['email'],
                $dati['username'], $dati['password'], true, 'SEGRETERIA', 0.0
            );
        } else {
            $utente = new EProprietario(
                $dati['nome'], $dati['cognome'], $dati['email'],
                $dati['username'], $dati['password'], true
            );
        }
        $em->persist($utente);
    }
    $em->flush();
    $em->commit();
    echo "Utenti demo inizializzati." . PHP_EOL;
} catch (\Throwable $e) {
    if ($em->getConnection()->isTransactionActive()) {
        $em->rollback();
    }
    throw $e;
}
