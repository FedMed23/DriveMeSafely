<?php

namespace CamassoMedelago\DriveMeSafely\Entity;

/**
 * Tipologia degli esami della Motorizzazione.
 *
 * - TEORIA: esame teorico
 * - PRATICA: esame pratico
 */
enum TipologiaEsame: string
{
    case TEORIA = 'Teoria';
    case PRATICA = 'Pratica';

    /**
     * Restituisce la descrizione della tipologia dell'esame.
     *
     * @return string
     */
    public function getDescrizione(): string
    {
        return $this->value;
    }
}
