<?php

namespace CamassoMedelago\DriveMeSafely\Service;

use CamassoMedelago\DriveMeSafely\Entity\EDomanda;
use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Entity\EQuiz;
use CamassoMedelago\DriveMeSafely\Entity\ESvolgimentoQuiz;
use CamassoMedelago\DriveMeSafely\Entity\ETentativoRisposta;
use CamassoMedelago\DriveMeSafely\Foundation\FDomanda;
use CamassoMedelago\DriveMeSafely\Foundation\FQuiz;
use CamassoMedelago\DriveMeSafely\Foundation\FSvolgimentoQuiz;
use CamassoMedelago\DriveMeSafely\Foundation\FTentativoRisposta;
use Doctrine\ORM\EntityManagerInterface;

class SQuiz
{
    private FQuiz $fQuiz;
    private FDomanda $fDomanda;
    private FSvolgimentoQuiz $fSvolgimento;
    private FTentativoRisposta $fTentativo;

    public function __construct(EntityManagerInterface $em)
    {
        $this->fQuiz = new FQuiz($em);
        $this->fDomanda = new FDomanda($em);
        $this->fSvolgimento = new FSvolgimentoQuiz($em);
        $this->fTentativo = new FTentativoRisposta($em);
    }

    /** @return EQuiz[] */
    public function getQuiz(): array
    {
        return $this->fQuiz->findAll();
    }

    public function getQuizById(int $idQuiz): EQuiz
    {
        $quiz = $this->fQuiz->findById($idQuiz);
        if ($quiz === null) {
            throw new \InvalidArgumentException('Quiz non trovato.');
        }
        return $quiz;
    }

    /** @return EDomanda[] */
    public function generaQuiz(int $idQuiz, EIscritto $iscritto): array
    {
        $quiz = $this->getQuizById($idQuiz);
        $tutte = $quiz->getDomande()->toArray();
        $viste = $this->fTentativo->findDomandeGiaSvolte($iscritto->getId());
        $errate = $this->fTentativo->findDomandeSbagliate($iscritto->getId());

        $nuove = [];
        $sbagliate = [];
        $corrette = [];
        foreach ($tutte as $domanda) {
            $id = $domanda->getIdDomanda();
            if (!in_array($id, $viste, true)) {
                $nuove[] = $domanda;
            } elseif (in_array($id, $errate, true)) {
                $sbagliate[] = $domanda;
            } else {
                $corrette[] = $domanda;
            }
        }

        shuffle($nuove);
        shuffle($sbagliate);
        shuffle($corrette);
        $numero = min($quiz->getNumeroDomande(), count($tutte));
        $risultato = [];
        $numeroNuove = (int) round($numero * 0.5);
        $numeroSbagliate = (int) round($numero * 0.3);
        $this->aggiungi($nuove, $risultato, $numeroNuove);
        $this->aggiungi($sbagliate, $risultato, $numeroNuove + $numeroSbagliate);
        $this->aggiungi($corrette, $risultato, $numero);
        shuffle($risultato);

        return $risultato;
    }

    public function correggiQuiz(
        int $idQuiz,
        EIscritto $iscritto,
        array $risposte
    ): ESvolgimentoQuiz {
        $quiz = $this->getQuizById($idQuiz);
        $svolgimento = new ESvolgimentoQuiz();
        $svolgimento->init($quiz, $iscritto, new \DateTimeImmutable(), 0, true);

        foreach ($risposte as $idDomanda => $valore) {
            if (!is_int($idDomanda) && !(is_string($idDomanda) && ctype_digit($idDomanda))) {
                continue;
            }
            $domanda = $this->fDomanda->findById((int) $idDomanda);
            if ($domanda === null) {
                continue;
            }
            if (!$quiz->getDomande()->contains($domanda)) {
                continue;
            }
            $risposta = filter_var($valore, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($risposta === null) {
                continue;
            }
            $tentativo = new ETentativoRisposta();
            $tentativo->init(
                $domanda,
                $svolgimento,
                $risposta,
                $risposta === $domanda->isRispostaCorretta()
            );
            $svolgimento->addTentativo($tentativo);
        }

        return $svolgimento;
    }

    public function confermaSvolgimento(ESvolgimentoQuiz $svolgimento): void
    {
        $this->fSvolgimento->save($svolgimento);
    }

    public function getSvolgimento(int $id, int $idIscritto): ESvolgimentoQuiz
    {
        $svolgimento = $this->fSvolgimento->getSvolgimentoPerId($id);
        if ($svolgimento === null) {
            throw new \InvalidArgumentException('Risultato quiz non trovato.');
        }
        if ($svolgimento->getIscritto()->getId() !== $idIscritto) {
            throw new \InvalidArgumentException('Risultato quiz non disponibile.');
        }
        return $svolgimento;
    }

    public function riepilogoQuiz(ESvolgimentoQuiz $svolgimento): array
    {
        $risposte = [];
        foreach ($svolgimento->getTentativi() as $tentativo) {
            $domanda = $tentativo->getDomanda();
            $risposte[] = [
                'domanda' => $domanda,
                'rispostaUtente' => $tentativo->isRispostaUtente(),
                'corretta' => $tentativo->isCorretta(),
            ];
        }

        return [
            'quiz' => $svolgimento->getQuiz(),
            'iscritto' => $svolgimento->getIscritto(),
            'data' => $svolgimento->getDataSvolgimento(),
            'errori' => $svolgimento->getErrori(),
            'superato' => $svolgimento->isSuperato(),
            'totaleDomande' => count($risposte),
            'risposte' => $risposte,
        ];
    }

    private function aggiungi(array $sorgente, array &$destinazione, int $limite): void
    {
        foreach ($sorgente as $domanda) {
            if (count($destinazione) >= $limite) {
                break;
            }
            if (!in_array($domanda, $destinazione, true)) {
                $destinazione[] = $domanda;
            }
        }
    }
}
