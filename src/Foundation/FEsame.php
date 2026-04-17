<?php
// src/Foundation/FEsame.php

namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\EEsame;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class FEsame
{
    private EntityManagerInterface $em;

    // Costruttore: collega Doctrine al database
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // ---------------------- SALVATAGGIO ----------------------
    // Salva un nuovo esame
    public function save(EEsame $esame): void
    {
        $this->em->persist($esame);
        $this->em->flush();
    }

    // ---------------------- LETTURA ----------------------
    // Recupera un esame tramite ID
    public function getEsameById(int $id): ?EEsame
    {
        return $this->em->getRepository(EEsame::class)->find($id);
    }

    // Recupera tutti gli esami
    public function getAllEsami(): array
    {
        return $this->em->getRepository(EEsame::class)->findAll();
    }

    // ---------------------- AGGIORNAMENTO ----------------------
    public function update(EEsame $esame): void
    {
        $this->em->flush();
    }

    // ---------------------- ELIMINAZIONE ----------------------
    public function delete(EEsame $esame): void
    {
        $this->em->remove($esame);
        $this->em->flush();
    }

    // ---------------------- METODI PERSONALIZZATI ----------------------

    // Trova esami per tipologia (teorico/pratico)
    public function getEsamiByTipologia(string $tipologia): array
    {
        return $this->em->getRepository(EEsame::class)->findBy([
            'tipologia' => $tipologia
        ]);
    }

    // Trova esami in una certa data
    public function getEsamiByData(\DateTimeImmutable $data): array
    {
        return $this->em->getRepository(EEsame::class)->findBy([
            'dataEs' => $data
        ]);
    }

    // Trova esami futuri (molto utile)
    public function getEsamiFuturi(): array
    {
        return $this->em->getRepository(EEsame::class)
            ->createQueryBuilder('e')
            ->where('e.dataEs > :oggi')
            ->setParameter('oggi', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }
}
?>
