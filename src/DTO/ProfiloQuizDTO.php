<?php

namespace CamassoMedelago\DriveMeSafely\DTO;

use CamassoMedelago\DriveMeSafely\Entity\EIscritto;

/**
 * Dati didattici usati dalla segreteria per valutare l'ammissione agli esami.
 */
class ProfiloQuizDTO
{
    public function __construct(
        private readonly EIscritto $iscritto,
        private readonly float $percentualeQuizSvolti,
        private readonly float $percentualeQuizSuperati,
        private readonly bool $idoneo
    ) {
    }

    public function getIscritto(): EIscritto
    {
        return $this->iscritto;
    }

    public function getPercentualeQuizSvolti(): float
    {
        return $this->percentualeQuizSvolti;
    }

    public function getPercentualeQuizSuperati(): float
    {
        return $this->percentualeQuizSuperati;
    }

    public function isIdoneo(): bool
    {
        return $this->idoneo;
    }
}
