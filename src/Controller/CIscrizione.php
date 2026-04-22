<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FPatente;
use CamassoMedelago\DriveMeSafely\Entity\EPatente;
use DateTimeImmutable;


class CIscrizione
{
    private FIscritto $fIscritto;
    private FPatente $fPatente;

    public function __construct(FIscritto $fIscritto, FPatente $fPatente)
    {
        $this->fIscritto = $fIscritto;
        $this->fPatente = $fPatente;
    }

    // Visualizza elenco pacchetti
    public function getPatenti(): array
    {
        return $this->fPatente->getAllPatenti();
    }

    // Seleziona pacchetto
    public function selezionaPatente(int $idPa): ?EPatente
    {   
    	return $this->fPatente->getPatenteById($idPa);
    }

    // Inserimento iscritto
    public function inserisciDati(array $datiIscritto): EIscritto
    {
        $iscritto = new EIscritto(
            $datiIscritto['nome'],
            $datiIscritto['cognome'],
            $datiIscritto['email'],
            $datiIscritto['username'],
            $datiIscritto['password'],
            $datiIscritto['stato'],
           
            $datiIscritto['codiceFiscale'],
            $datiIscritto['dataNascita'],
            $datiIscritto['luogoNascita'],
            $datiIscritto['indirizzo'],
            $datiIscritto['numeroTelefono'],
            $datiIscritto['tipoPatente'],
        );

        $this->fIscritto->save($iscritto);

        return $iscritto;
    }

    // Conferma dati
    public function confermaDati(int $idPa, EIscritto $iscritto): void
    {
	    // recupero patente
	    $patente = $this->fPatente->getPatenteById($idPa);

	    if (!$patente) {
	        throw new \Exception("Patente non trovata");
	    }

	    // assegno patente all'iscritto
	    $iscritto->setTipoPatente($patente);

	    // salvo modifiche
	    $this->fIscritto->update($iscritto);
	}
}
        

