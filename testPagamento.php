<?php

use CamassoMedelago\DriveMeSafely\Foundation\FPagamento;
use CamassoMedelago\DriveMeSafely\Foundation\FCartaDiCredito;
use CamassoMedelago\DriveMeSafely\Controller\CPagamento;

require_once __DIR__ . "/vendor/autoload.php";

// carica EntityManager
$entityManager = require __DIR__ . "/bootstrap.php";

// crea foundation
$fPagamento = new CamassoMedelago\DriveMeSafely\Foundation\FPagamento($entityManager);
$fCarta = new CamassoMedelago\DriveMeSafely\Foundation\FCartaDiCredito($entityManager);


// crea controller
$controller = new CPagamento($fPagamento, $fCarta);

// -------- TEST --------

// 1. recupera pagamenti utente
$pagamenti = $controller->getPagamenti(1);

// 2. seleziona pagamento
$pagamento = $controller->selezionaPagamento($pagamenti[0]->getId());
echo "Numero pagamenti: " . count($pagamenti);


// 4. inserisci carta
$carta = $controller->inserisciCarta([
    'numero' => '1234567890123456',
    'nome' => 'Federica',
    'cognome' => 'Rossi',
    'scadenza' => new DateTimeImmutable('2027-12-01')
]);

// 5. conferma pagamento
$controller->confermaPagamento($pagamento->getId(), $carta);

echo "TEST COMPLETATO";