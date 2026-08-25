<?php

namespace CamassoMedelago\DriveMeSafely\Entity;

/**
 * Elenco tassonomico fisso dei capitoli del manuale della patente B.
 *
 * @author Camasso-Medelago
 */
enum ArgomentoMinisteriale: string
{
    case SEGNALETICA = 'Segnaletica Stradale';

    case PRECEDENZE = 'Incroci e Precedenze';

    case VELOCITA = 'Velocità e Distanza di Sicurezza';

    case COMPORTAMENTO = 'Norme di Comportamento e Sinistri';

    case STATO_PSICOFISICO = 'Stato Psicofisico, Alcol e Farmaci';

    case MECCANICA = 'Elementi del Veicolo e Manutenzione';

    /**
     * Restituisce la descrizione estesa dell'argomento.
     */
    public function getDescrizione(): string
    {
        return $this->value;
    }
}
