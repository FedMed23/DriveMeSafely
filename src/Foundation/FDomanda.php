<?php
// src/Foundation/FDomanda.php

namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\EDomanda;
use Doctrine\ORM\EntityManagerInterface;

class FDomanda
{
    private EntityManagerInterface $em;

    // ---------------------- COSTRUTTORE ----------------------
    /**
     * Costruttore: collega Doctrine al database
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // ---------------------- SALVATAGGIO ----------------------
    /**
     * Salva una nuova domanda
     */
    public function save(EDomanda $domanda): void
    {
        $this->em->persist($domanda);
        $this->em->flush();
    }

    // ---------------------- LETTURA ----------------------
    /**
     * Recupera una domanda tramite ID
     */
    public function findById(int $id): ?EDomanda
    {
        return $this->em->find(EDomanda::class, $id);
    }

    /**
     * Recupera tutte le domande ordinate
     * per ID crescente.
     */
    public function findAll(): array
    {
        return $this->em
            ->getRepository(EDomanda::class)
            ->createQueryBuilder('d')
            ->orderBy('d.idDomanda', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recupera tutte le domande appartenenti
     * a uno specifico argomento.
     *
     * Il confronto ignora maiuscole/minuscole
     * e spazi iniziali/finali.
     */
    public function findByArgomento(string $argomento): array
    {
        return $this->em
            ->getRepository(EDomanda::class)
            ->createQueryBuilder('d')
            ->where('LOWER(TRIM(d.argomento)) = LOWER(TRIM(:argomento))')
            ->setParameter('argomento', $argomento)
            ->getQuery()
            ->getResult();
    }

    // ---------------------- AGGIORNAMENTO ----------------------
    /**
     * Aggiorna una domanda esistente
     */
    public function update(EDomanda $domanda): void
    {
        $this->em->flush();
    }

    // ---------------------- ELIMINAZIONE ----------------------
    /**
     * Elimina una domanda
     */
    public function delete(EDomanda $domanda): void
    {
        $this->em->remove($domanda);
        $this->em->flush();
    }
}
?>
