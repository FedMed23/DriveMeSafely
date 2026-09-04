<?php

namespace CamassoMedelago\DriveMeSafely\Entity;

enum EAula: string
{
    case AULA_A = 'AULA_A';
    case AULA_B = 'AULA_B';

    public function getNomeEsteso(): string
    {
        return match ($this) {
            self::AULA_A => 'Aula A (Sede Centrale - Piano Terra)',
            self::AULA_B => 'Aula B (Sede Centrale - Primo Piano)',
        };
    }

    /**
     * Ritorna la capienza massima dell'aula.
     */
    public function getCapienzaMassima(): int
    {
        return match ($this) {
            self::AULA_A => 30,
            self::AULA_B => 25,
        };
    }
}
