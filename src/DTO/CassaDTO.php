<?php

namespace CamassoMedelago\DriveMeSafely\DTO;

use CamassoMedelago\DriveMeSafely\Entity\EPagamento;

class CassaDTO
{
    /**
     * @param EPagamento[] $pagamenti
     */
    public function __construct(
        private array $pagamenti,
        private float $entrate,
        private float $uscite
    ) {
    }

    /** @return EPagamento[] */
    public function getPagamenti(): array
    {
        return $this->pagamenti;
    }

    public function getEntrate(): float
    {
        return $this->entrate;
    }

    public function getUscite(): float
    {
        return $this->uscite;
    }
}

