<?php

namespace CamassoMedelago\DriveMeSafely\Service;

use CamassoMedelago\DriveMeSafely\Entity\EDomanda;
use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Entity\EQuiz;
use CamassoMedelago\DriveMeSafely\Entity\ESvolgimentoQuiz;
use CamassoMedelago\DriveMeSafely\Entity\ETentativoRisposta;
use CamassoMedelago\DriveMeSafely\DTO\EsitoQuizDTO;
use CamassoMedelago\DriveMeSafely\Foundation\FDomanda;
use CamassoMedelago\DriveMeSafely\Foundation\FQuiz;
use CamassoMedelago\DriveMeSafely\Foundation\FSvolgimentoQuiz;
use CamassoMedelago\DriveMeSafely\Foundation\FTentativoRisposta;
use Doctrine\ORM\EntityManagerInterface;

//Service che implementa la gestione dei quiz 
class SQuiz
{
    private EntityManagerInterface $em;
    private FQuiz $fQuiz;
    private FDomanda $fDomanda;
    private FSvolgimentoQuiz $fSvolgimento;
    private FTentativoRisposta $fTentativo;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->fQuiz = new FQuiz($em);
        $this->fDomanda = new FDomanda($em);
        $this->fSvolgimento = new FSvolgimentoQuiz($em);
        $this->fTentativo = new FTentativoRisposta($em);
    }

    //1)Metodo che riporta tutti i quiz disponibili nella scuolaguida
    public function getQuiz(): array
    {
        return $this->fQuiz->findAll();
    }

    /**
     * Statistiche sintetiche mostrate nella dashboard personale dell'allievo.
     */
    public function getStatisticheAllievo(EIscritto $iscritto): array
    {
        $idIscritto = $iscritto->getId();
        $totale = $this->fSvolgimento->contaQuizSvoltiByIscritto($idIscritto);
        $superati = $this->fSvolgimento->contaQuizSuperatiByIscritto($idIscritto);

        return [
            'totale' => $totale,
            'superati' => $superati,
            'nonSuperati' => max(0, $totale - $superati),
            'percentualeIdoneita' => $totale === 0
                ? 0
                : (int) round(($superati / $totale) * 100),
        ];
    }

    //2)Metodo che riporta i dettagli del quiz scelto dall'utente
    public function getQuizById(int $idQuiz): EQuiz
    {
        $quiz = $this->fQuiz->findById($idQuiz);
        if ($quiz === null) {
            throw new \InvalidArgumentException('Quiz non trovato.');
        }
        return $quiz;
    }

    //3)Metodo che genera il quiz da sottoporre all'utente e seleziona le domande in base ad una logica di priorità a cascata
    public function generaQuiz(int $idQuiz, EIscritto $iscritto): array
    {
        $quiz = $this->getQuizById($idQuiz);
        $tutte = $quiz->getDomande()->toArray();
        $targetDomande = min($quiz->getNumeroDomande(), count($tutte));

        if ($targetDomande === 0) {
            return [];
        }

        $viste = $this->fTentativo->findDomandeGiaSvolte($iscritto->getId());
        $errate = $this->fTentativo->findDomandeSbagliate($iscritto->getId());

        $nuove = [];
        $sbagliate = [];
        $corrette = [];

        //Suddivisione delle domande in nuove, sbagliate e corrette
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

        //Mescolamento delle domande per categoria
        shuffle($nuove);
        shuffle($sbagliate);
        shuffle($corrette);

        $risultato = [];
        $quotaNuove = (int) round($targetDomande * 0.5);
        $quotaSbagliate = (int) round($targetDomande * 0.3);

        // 1. Inserimento prioritario con quote consigliate (50% nuove, 30% sbagliate, 20% corrette)
        $this->aggiungi($nuove, $risultato, $quotaNuove);
        $this->aggiungi($sbagliate, $risultato, count($risultato) + $quotaSbagliate);
        $this->aggiungi($corrette, $risultato, $targetDomande);

        // 2. Cascata di fallback: se il target non è ancora raggiunto, prendi da qualsiasi categoria con domande residue
        if (count($risultato) < $targetDomande) {
            $this->aggiungi($nuove, $risultato, $targetDomande);
        }
        if (count($risultato) < $targetDomande) {
            $this->aggiungi($sbagliate, $risultato, $targetDomande);
        }
        if (count($risultato) < $targetDomande) {
            $this->aggiungi($corrette, $risultato, $targetDomande);
        }

        shuffle($risultato);

        return $risultato;
    }

    //4)Metodo che restituisce lo svolgimento del quiz
    public function correggiQuiz(
        int $idQuiz,
        EIscritto $iscritto,
        array $risposte,
        array $idDomandeSelezionate = []
    ): ESvolgimentoQuiz {
        $quiz = $this->getQuizById($idQuiz);
        $svolgimento = new ESvolgimentoQuiz();
        $svolgimento->init($quiz, $iscritto, new \DateTimeImmutable(), 0, true);

        if ($idDomandeSelezionate !== []) {
            $idDomande = array_map('intval', $idDomandeSelezionate);
            $idDomande = array_values(array_unique($idDomande));
        } else {
            $idDomande = array_map('intval', array_keys($risposte));
        }

        $totaleDomandeScheda = 0;
        $errori = 0;

        foreach ($idDomande as $idDomanda) {
            $domanda = $this->fDomanda->findById($idDomanda);
            if ($domanda === null) {
                continue;
            }
            if (!$quiz->getDomande()->contains($domanda)) {
                continue;
            }

            $totaleDomandeScheda++;
            $valore = $risposte[$idDomanda] ?? $risposte[(string) $idDomanda] ?? null;

            if ($valore === null || $valore === '') {
                // Domanda omessa (o tempo scaduto)
                $errori++;
                continue;
            }

            if (is_bool($valore)) {
                $risposta = $valore;
            } elseif ($valore === 'true' || $valore === '1' || $valore === 1) {
                $risposta = true;
            } elseif ($valore === 'false' || $valore === '0' || $valore === 0) {
                $risposta = false;
            } else {
                // Valore non valido / anomalo -> trattato come omessa/errore
                $errori++;
                continue;
            }

            $isEsatta = ($risposta === $domanda->isRispostaCorretta());
            if (!$isEsatta) {
                $errori++;
            }

            $tentativo = new ETentativoRisposta();
            $tentativo->init(
                $domanda,
                $svolgimento,
                $risposta,
                $isEsatta
            );
            $svolgimento->addTentativo($tentativo);
        }

        $svolgimento->setErrori($errori);
        $svolgimento->setSuperato($errori <= 3);

        return $svolgimento;
    }

    //5)Metodo che conferma lo svolgimento del quiz e salva i risultati nel database
    public function confermaSvolgimento(ESvolgimentoQuiz $svolgimento): void
    {
        $this->em->beginTransaction();
        try {
            $this->fSvolgimento->save($svolgimento);
            $this->em->commit();
        } catch (\Throwable $e) {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->rollback();
            }
            throw $e;
        }
    }

    //Metodi ausiliari
    
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

    public function getEsitoQuiz(int $id, int $idIscritto): EsitoQuizDTO
    {
        return EsitoQuizDTO::fromSvolgimento($this->getSvolgimento($id, $idIscritto));
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
