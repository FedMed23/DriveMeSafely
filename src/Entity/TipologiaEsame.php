<?php

namespace CamassoMedelago\DriveMeSafely\Entity;

enum TipologiaEsame: string
{
    case TEORIA = 'TEORIA';
    case PRATICA = 'PRATICA';

    public function getDescrizione(): string
    {
        return $this === self::TEORIA ? 'Teoria' : 'Pratica';
    }
}
