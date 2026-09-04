<?php

namespace CamassoMedelago\DriveMeSafely\Service;

use CamassoMedelago\DriveMeSafely\DTO\ProfiloQuizDTO;
use CamassoMedelago\DriveMeSafely\Entity\EEsame;
use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Entity\TipologiaEsame;
use CamassoMedelago\DriveMeSafely\Foundation\FEsame;
use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FPrenotazioneEsami;
use CamassoMedelago\DriveMeSafely\Foundation\FQuiz;
use CamassoMedelago\DriveMeSafely\Foundation\FSvolgimentoQuiz;
use Doctrine\ORM\EntityManagerInterface;

class SIdoneitaEsame
{
    private const SOGLIA_QUIZ_SVOLTI = 70.0;
    private const SOGLIA_QUIZ_SUPERATI = 70.0;

    private FIscritto $fIscritto;
    private FQuiz $fQuiz;
    private FSvolgimentoQuiz $fSvolgimenti;
    private FEsame $fEsame;
    private FPrenotazioneEsami $fPrenotazioni;

    public function __construct(
        EntityManagerInterface $em,
        ?FIscritto $fIscritto = null,
        ?FQuiz $fQuiz = null,
        ?FSvolgimentoQuiz $fSvolgimenti = null,
        ?FEsame $fEsame = null,
        ?FPrenotazioneEsami $fPrenotazioni = null
    ) {
        $this->fIscritto = $fIscritto ?? new FIscritto($em);
        $this->fQuiz = $fQuiz ?? new FQuiz($em);
        $this->fSvolgimenti = $fSvolgimenti ?? new FSvolgimentoQuiz($em);
        $this->fEsame = $fEsame ?? new FEsame($em);
        $this->fPrenotazioni = $fPrenotazioni ?? new FPrenotazioneEsami($em);
    }

    /**
     * @return ProfiloQuizDTO[]
     */
    public function getProfiliQuizIscritti(): array
    {
        $totaleQuiz = $this->fQuiz->contaQuiz();
        $profili = [];

        foreach ($this->fIscritto->findAttivi() as $iscritto) {
            if (!$iscritto instanceof EIscritto) {
                continue;
            }

            [$percentualeSvolti, $percentualeSuperati] = $this->calcolaPercentuali(
                $iscritto->getId(),
                $totaleQuiz
            );

            $profili[] = new ProfiloQuizDTO(
                $iscritto,
                $percentualeSvolti,
                $percentualeSuperati,
                $this->superaSoglie($percentualeSvolti, $percentualeSuperati)
            );
        }

        return $profili;
    }

    /**
     * Restituisce gli iscritti attivi con i requisiti didattici per la sessione.
     *
     * @return EIscritto[]
     */
    public function getIscrittiIdonei(int $idEsame): array
    {
        if ($idEsame <= 0) {
            throw new \InvalidArgumentException('Identificativo esame non valido.');
        }

        $esame = $this->fEsame->findById($idEsame);
        if (!$esame instanceof EEsame) {
            throw new \InvalidArgumentException('Sessione d\'esame non trovata.');
        }

        $totaleQuiz = $this->fQuiz->contaQuiz();
        if ($totaleQuiz === 0) {
            return [];
        }

        $idonei = [];
        foreach ($this->fIscritto->findAttivi() as $iscritto) {
            if (!$iscritto instanceof EIscritto || !$this->isIdoneo($iscritto, $esame, $totaleQuiz)) {
                continue;
            }

            $dataEsame = \DateTimeImmutable::createFromMutable($esame->getDataEs());
            if (!$this->fPrenotazioni->haEsameInOrario($iscritto->getId(), $dataEsame)) {
                $idonei[] = $iscritto;
            }
        }

        return $idonei;
    }

    public function isIdoneo(EIscritto $iscritto, EEsame $esame, ?int $totaleQuiz = null): bool
    {
        $totaleQuiz ??= $this->fQuiz->contaQuiz();
        if ($totaleQuiz === 0) {
            return false;
        }

        [$percentualeSvolti, $percentualeSuperati] = $this->calcolaPercentuali(
            $iscritto->getId(),
            $totaleQuiz
        );

        if (!$this->superaSoglie($percentualeSvolti, $percentualeSuperati)) {
            return false;
        }

        return $esame->getTipologia() !== TipologiaEsame::PRATICA
            || $this->fPrenotazioni->haSuperatoEsameTeorico($iscritto->getId());
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function calcolaPercentuali(int $idIscritto, int $totaleQuiz): array
    {
        $quizSvolti = $this->fSvolgimenti->contaQuizSvoltiByIscritto($idIscritto);
        $quizSuperati = $this->fSvolgimenti->contaQuizSuperatiByIscritto($idIscritto);

        $percentualeSvolti = $totaleQuiz > 0
            ? ($quizSvolti * 100.0) / $totaleQuiz
            : 0.0;
        $percentualeSuperati = $quizSvolti > 0
            ? ($quizSuperati * 100.0) / $quizSvolti
            : 0.0;

        return [$percentualeSvolti, $percentualeSuperati];
    }

    private function superaSoglie(float $percentualeSvolti, float $percentualeSuperati): bool
    {
        return $percentualeSvolti >= self::SOGLIA_QUIZ_SVOLTI
            && $percentualeSuperati >= self::SOGLIA_QUIZ_SUPERATI;
    }
}
