<?php

namespace CamassoMedelago\DriveMeSafely\Entity;

enum EArgomentoMinisteriale: string
{
    case SEGNALETICA = 'SEGNALETICA';
    case PRECEDENZE = 'PRECEDENZE';
    case VELOCITA = 'VELOCITA';
    case COMPORTAMENTO = 'COMPORTAMENTO';
    case STATO_PSICOFISICO = 'STATO_PSICOFISICO';
    case MECCANICA = 'MECCANICA';

    public function getDescrizione(): string
    {
        return match ($this) {
            self::SEGNALETICA => 'Segnaletica Stradale',
            self::PRECEDENZE => 'Incroci e Precedenze',
            self::VELOCITA => 'Velocità e Distanza di Sicurezza',
            self::COMPORTAMENTO => 'Norme di Comportamento e Sinistri',
            self::STATO_PSICOFISICO => 'Stato Psicofisico, Alcol e Farmaci',
            self::MECCANICA => 'Elementi del Veicolo e Manutenzione',
        };
    }
}
