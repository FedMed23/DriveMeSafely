<?php

namespace CamassoMedelago\DriveMeSafely\Entity;

/**
 * Elenco fisso e controllato delle aule fisiche presenti nell'autoscuola.
 * Impedisce refusi e ottimizza la pianificazione logistica del palinsesto.
 *
 * @author Camasso-Medelago
 */
enum Aula: string
{
    case AULA_A = 'Aula A (Sede Centrale - Piano Terra)';
    case AULA_B = 'Aula B (Sede Centrale - Primo Piano)';

    /**
     * Restituisce il nome esteso dell'aula.
     */
    public function getNomeEsteso(): string
    {
        return $this->value;
    }
}
