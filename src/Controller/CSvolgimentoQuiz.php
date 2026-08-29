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
    private FQuiz $fQuiz;
    private FSvolgimentoQuiz $fSvolgimentoQuiz;
  
  
    public function __construct(FIscritto $fIscritto, FQuiz $fQuiz, FSvolgimentoQuiz $fSvolgimentoQuiz)
    {
        $this->fIscritto = $fIscritto;
        $this->fQuiz = $fQuiz;
        $this->fSvolgimentoQuiz = $fSvolgimentoQuiz;
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
    public function svolgiQuiz(int $idQuiz, EIscritto $iscritto, array $risposteUtente): ESvolgimentoQuiz {

        $quiz = $this->fQuiz->findById($idQuiz);
        if (!$quiz) {
            throw new \Exception("Quiz non trovato");
        }
        
        $svolgimento = new ESvolgimentoQuiz( $quiz, $iscritto, $risposteUtente);

        $this->fSvolgimentoQuiz->save($svolgimento);

        return $svolgimento;

    }

   public function riepilogoQuiz(ESvolgimentoQuiz $svolgimento): array
{
    $riepilogoRisposte = [];

    foreach ($svolgimento->getTentativiRisposta() as $tentativo) {

        $domanda = $tentativo->getDomanda();

        $riepilogoRisposte[] = [
            'idDomanda' => $domanda->getId(),
            'contenuto' => $domanda->getContenuto(),

            // risposta corretta della domanda
            'rispostaCorretta' => $domanda->getRispostaCorretta(),

            // risposta data dall'utente
            'rispostaUtente' => $tentativo->getRispostaUtente(),

            // esito singola domanda
            'corretta' => $tentativo->isCorretta()
        ];
    }

    return [
        'quiz' => $svolgimento->getQuiz()->getNomeQuiz(),

        'data' => $svolgimento
            ->getDataSvolgimento()
            ->format('d/m/Y H:i'),

        'errori' => $svolgimento->getErrori(),

        'superato' => $svolgimento->isSuperato(),

        'totaleDomande' => count($svolgimento->getTentativiRisposta()),

        'risposte' => $riepilogoRisposte
    ];
}
        
