<?php

namespace CamassoMedelago\DriveMeSafely\Service;

use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service che implementa la logica per la consultazione del profilo
 * dell'utente registrato (iscritto). Sola lettura: nessuna modifica
 * dei dati anagrafici è prevista da questo caso d'uso.
 */
class SProfilo
{
    private FIscritto $fIscritto;

    public function __construct(EntityManagerInterface $em)
    {
        $this->fIscritto = new FIscritto($em);
    }

    /**
     * Recupera i dati del profilo dell'iscritto in sessione.
     *
     * @throws \InvalidArgumentException se l'iscritto non esiste.
     */
    public function getProfilo(int $idIscritto): EIscritto
    {
        $iscritto = $this->fIscritto->findById($idIscritto);
        if ($iscritto === null) {
            throw new \InvalidArgumentException('Utente non trovato.');
        }

        return $iscritto;
    }
}
