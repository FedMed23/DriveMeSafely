<?php
// src/Foundation/FEsame.php

namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\EEsame;
use Doctrine\ORM\EntityManagerInterface;

class FEsame
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
     * Salva un nuovo esame
     */
    public function save(EEsame $esame): void
    {
        $this->em->persist($esame);
        $this->em->flush();
    }

    // ---------------------- LETTURA ----------------------
    /**
     * Recupera un esame tramite ID
     */
    public function findById(int $idEsame): ?EEsame
    {
        return $this->em->find(EEsame::class, $idEsame);
    }

    /**
     * Recupera tutte le sessioni d'esame future,
     * ordinate dalla più vicina alla più lontana.
     */
    public function findSessioniFuture(): array
    {
        return $this->em
            ->getRepository(EEsame::class)
            ->createQueryBuilder('e')
            ->where('e.dataEs >= :oggi')
            ->setParameter('oggi', new \DateTimeImmutable())
            ->orderBy('e.dataEs', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recupera tutte le sessioni d'esame future
     * filtrate per tipologia (TEORIA o PRATICA).
     */
    public function findFutureByTipologia(string $tipologia): array
    {
        return $this->em
            ->getRepository(EEsame::class)
            ->createQueryBuilder('e')
            ->where('e.tipologia = :tipo')
            ->andWhere('e.dataEs >= :oggi')
            ->setParameter('tipo', $tipologia)
            ->setParameter('oggi', new \DateTimeImmutable())
            ->orderBy('e.dataEs', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // ---------------------- AGGIORNAMENTO ----------------------
    /**
     * Aggiorna un esame esistente
     */
    public function update(EEsame $esame): void
    {
        $this->em->flush();
    }

    // ---------------------- ELIMINAZIONE ----------------------
    /**
     * Elimina un esame
     */
    public function delete(EEsame $esame): void
    {
        $this->em->remove($esame);
        $this->em->flush();
    }
}
?>
