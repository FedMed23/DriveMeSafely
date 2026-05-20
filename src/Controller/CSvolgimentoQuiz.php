<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FQuiz;
use CamassoMedelago\DriveMeSafely\Entity\EQuiz;
use CamassoMedelago\DriveMeSafely\Entity\ESvolgimentoQuiz;
use CamassoMedelago\DriveMeSafely\Foundation\FSvolgimentoQuiz;
use CamassoMedelago\DriveMeSafely\Entity\ETentativoRisposta;
use CamassoMedelago\DriveMeSafely\Foundation\FTentativoRisposta;
use CamassoMedelago\DriveMeSafely\Entity\EDomanda;
use CamassoMedelago\DriveMeSafely\Foundation\FDomanda;


use DateTimeImmutable;


class CSvolgimentoQuiz
{
    private FIscritto $fIscritto;
    private FQuiz fQuiz;
  
    private array $tentativiRisposta = [];
    private FSvolgimentoQuiz fSvolgimentoQuiz;
  
  
    public function __construct(FIscritto $fIscritto, FQuiz fQuiz)
    {
        $this->fIscritto = $fIscritto;
        $this->fQuiz = $fQuiz;
    }

    // Visualizza elenco quiz
    public function getQuiz(): array
    {
        return $this->fQuiz->findAll();
    }

    // Seleziona quiz
    public function selezionaQuiz(int $idQuiz): ?EQuiz
    {   
    	return $this->fQuiz->findById($idQuiz);
    }
  
    // Svolgimento quiz
    public function svolgimentoQuiz(EIscritto $fIscritto, EQuiz fQuiz, ETentativoRisposta $fTentativoRisposta): ESvolgimentoQuiz
    {
        $svolgimentoQuiz = new ESvolgimentoQuiz(FIscritto $fIscritto, FQuiz fQuiz, FTentativoRisposta $fTentativoRisposta);
      
        $this->fSvolgimentoQuiz->save($SvolgimentoQuiz);

        return $svolgimentoQuiz;
    }

   /* Conferma dati
    public function confermaDati(ESvolgimentoQuiz $ESvolgimentoQuiz, EIscritto $iscritto): void
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
  */
}
        