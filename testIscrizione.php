<?php

use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FPatente;
use CamassoMedelago\DriveMeSafely\Controller\CIscrizione;

require_once __DIR__ . "/vendor/autoload.php";

// carica EntityManager
$entityManager = require __DIR__ . "/bootstrap.php";

// crea foundation
$fIscritto = new CamassoMedelago\DriveMeSafely\Foundation\FIscritto($entityManager);
$fPatente = new CamassoMedelago\DriveMeSafely\Foundation\FPatente($entityManager);
// crea controller
$controller = new CIscrizione($fIscritto, $fPatente);

//--------Test---------
//1-recupero tipologie patenti

$patenti = $controller->getPatenti();
echo "Numero patenti: " . count($patenti);

//2-seleziona patente

$patente = $controller->selezionaPatente($patenti[1]->getId());


//3-Inserimento dati
$martina = $entityManager->getRepository(
    CamassoMedelago\DriveMeSafely\Entity\EUtenteRegistrato::class
)->find(1);

$iscritto = $controller -> inserisciDati([
				     'nome' => $martina->getNomeUtente(),
				     'cognome' => $martina->getCognomeUtente(),
				     'email' => $martina->getEmail(),
				     'username' => $martina->getUsername(),
				     'password' => $martina->getPasswordHash(),
				     'stato' => $martina->getStatoUtente(),
	                 'codiceFiscale'=> 'CMSMTN03D48A515Z',
		             'dataNascita'=> new DateTimeImmutable('2003-12-01'),
		             'luogoNascita'=> 'Avezzano',
		             'indirizzo'=> 'Via dei Gigli, 18',
		             'numeroTelefono' => '3395174926',
		             'tipoPatente' => $patente
]);

// 5. conferma iscrizione
$controller->confermaDati($patente->getId(), $iscritto);


echo "TEST COMPLETATO";