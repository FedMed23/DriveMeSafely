<?php
// src/Foundation/FGuida.php

namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\EGuida;
use CamassoMedelago\DriveMeSafely\Entity\EDipendente;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;


class FGuida
{
    private EntityManagerInterface $em;

    // Costruttore
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // ---------------------- SALVATAGGIO ----------------------
    public function save(EGuida $guida): void
    {
        $this->em->persist($guida);
        $this->em->flush();
    }

    // ---------------------- LETTURA ----------------------
    // Recupera per ID
    public function getGuidaById(int $id): ?EGuida
    {
        return $this->em->getRepository(EGuida::class)->find($id);
    }

    // Recupera tutte le guide
    public function getAllGuide(): array
    {
        return $this->em->getRepository(EGuida::class)->findAll();
    }

    // ---------------------- AGGIORNAMENTO ----------------------
    public function update(EGuida $guida): void
    {
        $this->em->flush();
    }

    // ---------------------- ELIMINAZIONE ----------------------
    public function delete(EGuida $guida): void
    {
        $this->em->remove($guida);
        $this->em->flush();
    }

    // ---------------------- METODI PERSONALIZZATI ----------------------

    // Trova guide per dipendente
    public function getGuideByDipendente(EDipendente $dipendente): array
    {
        return $this->em->getRepository(EGuida::class)->findBy([
            'idDipendente' => $dipendente
        ]);
    }

    // Trova guide in una certa data
    public function getGuideByData(\DateTimeImmutable $data): array
    {
        return $this->em->getRepository(EGuida::class)
            ->createQueryBuilder('g')
            ->where('DATE(g.dataOra) = :data')
            ->setParameter('data', $data->format('Y-m-d'))
            ->getQuery()
            ->getResult();
    }

    // Trova guide future
    public function getGuideFuture(): array
    {
        return $this->em->getRepository(EGuida::class)
            ->createQueryBuilder('g')
            ->where('g.dataOra > :oggi')
            ->setParameter('oggi', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }
}
?>
