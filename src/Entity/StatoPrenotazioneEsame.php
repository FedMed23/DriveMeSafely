<?php

namespace CamassoMedelago\DriveMeSafely\Entity;

enum StatoPrenotazioneEsame: string
{
    case PRENOTATO = 'PRENOTATO';
    case EFFETTUATO = 'EFFETTUATO';
    case ANNULLATO = 'ANNULLATO';
    case NO_SHOW = 'NO_SHOW';

    public function getDescrizione(): string
    {
        return match ($this) {
            self::PRENOTATO => 'Prenotato',
            self::EFFETTUATO => 'Effettuato',
            self::ANNULLATO => 'Annullato',
            self::NO_SHOW => 'Assente non giustificato',
        };
    }

    public function isAttiva(): bool
    {
        return $this === self::PRENOTATO;
    }
}
