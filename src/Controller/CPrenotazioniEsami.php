<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FQuiz;
use CamassoMedelago\DriveMeSafely\Foundation\FEsame;

use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Entity\EQuiz;
use CamassoMedelago\DriveMeSafely\Entity\EEsame;

use DateTimeImmutable;

class CPrenotazioniEsami
{
    private FIscritto $fIscritto;
    private FQuiz $fQuiz;
    private FEsame $fEsame;

    public function __construct(
        FIscritto $fIscritto,
        FQuiz $fQuiz,
        FEsame $fEsame
    ) {
        $this->fIscritto = $fIscritto;
        $this->fQuiz = $fQuiz;
        $this->fEsame = $fEsame;
    }
  
  // Visualizza elenco studenti iscritti
    public function getStudenti(): array
    {
        return $this->fIscritto->getAllIscritti();
    }
  
 // Visualizza esiti quiz dello studente selezionato
    public function visualizzaEsitiQuiz(int $idQuiz): ?EQuiz
    {
        return $this->fQuiz->findById($idQuiz);
    }

    // Segnalazione studente alla segreteria
    // per prenotazione esame teorico/pratico
    public function segnalaStudenteEsame(array $datiEsame): EEsame
    {
        // Recupero studente
        $iscritto = $this->fIscritto->getIscrittoById(
            $datiEsame['idIscritto']
        );

        if (!$iscritto) {
            throw new \Exception("Studente non trovato");
        }

        // Creazione esame
        $esame = new EEsame(
            $datiEsame['tipologia'],
            new DateTimeImmutable($datiEsame['dataEsame'])
        );

        // Salvataggio esame
        $this->fEsame->save($esame);

        return $esame;
    }

    // Conferma segnalazione
    public function confermaSegnalazione(
        EIscritto $iscritto,
        EEsame $esame
    ): string {

        return "Lo studente "
            . $iscritto->getNome()
            . " "
            . $iscritto->getCognome()
            . " è stato segnalato alla segreteria "
            . "per l'esame "
            . $esame->getTipologia()
            . " del giorno "
            . $esame->getDataEsame()->format('d/m/Y');
    }
}
?>


