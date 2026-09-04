<?php

namespace CamassoMedelago\DriveMeSafely\Entity;

enum StatoPrenotazione: string
{
    case PRENOTATA = 'PRENOTATA';
    case EFFETTUATA = 'EFFETTUATA';
    case ANNULLATA = 'ANNULLATA';
    case NO_SHOW = 'NO_SHOW';

    public function getDescrizione(): string
    {
        return match ($this) {
            self::PRENOTATA => 'Prenotata',
            self::EFFETTUATA => 'Effettuata',
            self::ANNULLATA => 'Annullata',
            self::NO_SHOW => 'Assente non giustificato',
        };
    }

    public function isAttiva(): bool
    {
        return $this === self::PRENOTATA;
    }
}
