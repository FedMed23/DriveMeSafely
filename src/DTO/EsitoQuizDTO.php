<?php

namespace CamassoMedelago\DriveMeSafely\DTO;

use CamassoMedelago\DriveMeSafely\Entity\ESvolgimentoQuiz;

/**
 * DTO che impacchetta l'esito di uno svolgimento quiz per la view,
 * evitando che il Service costruisca a mano un array associativo.
 */
class EsitoQuizDTO
{
    /**
     * @param array<int, array{domanda: \CamassoMedelago\DriveMeSafely\Entity\EDomanda, rispostaUtente: bool, corretta: bool}> $risposte
     */
    private function __construct(
        private readonly ESvolgimentoQuiz $svolgimento,
        private readonly array $risposte
    ) {
    }

    public static function fromSvolgimento(ESvolgimentoQuiz $svolgimento): self
    {
        $risposte = [];
        foreach ($svolgimento->getTentativi() as $tentativo) {
            $risposte[] = [
                'domanda' => $tentativo->getDomanda(),
                'rispostaUtente' => $tentativo->isRispostaUtente(),
                'corretta' => $tentativo->isCorretta(),
            ];
        }

        return new self($svolgimento, $risposte);
    }

    public function getQuiz(): \CamassoMedelago\DriveMeSafely\Entity\EQuiz
    {
        return $this->svolgimento->getQuiz();
    }

    public function getIscritto(): \CamassoMedelago\DriveMeSafely\Entity\EIscritto
    {
        return $this->svolgimento->getIscritto();
    }

    public function getData(): \DateTimeImmutable
    {
        return $this->svolgimento->getDataSvolgimento();
    }

    public function getErrori(): int
    {
        return $this->svolgimento->getErrori();
    }

    public function isSuperato(): bool
    {
        return $this->svolgimento->isSuperato();
    }

    public function getTotaleDomande(): int
    {
        return count($this->risposte);
    }

    /**
     * @return array<int, array{domanda: \CamassoMedelago\DriveMeSafely\Entity\EDomanda, rispostaUtente: bool, corretta: bool}>
     */
    public function getRisposte(): array
    {
        return $this->risposte;
    }
}
