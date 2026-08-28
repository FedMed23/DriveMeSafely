<?php

namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\EPatente;
use Doctrine\ORM\EntityManagerInterface;

class FPatente
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Recupera una patente tramite il suo identificativo.
     */
    public function findById(int $idPa): ?EPatente
    {
        return $this->em->getRepository(EPatente::class)->find($idPa);
    }

    /**
     * Recupera una patente tramite il tipo associato.
     */
    public function findByTipo(string $tipo): ?EPatente
    {
        return $this->em->getRepository(EPatente::class)->findOneBy(['tipo' => $tipo]);
    }

    /**
     * Recupera tutte le patenti ordinate per ID.
     */
    public function findAll(): array
    {
        return $this->em->getRepository(EPatente::class)->findBy([], ['idPa' => 'ASC']);
    }

    /**
     * Recupera una patente insieme alle spese associate.
     */
    public function findPacchettoById(int $idPa): ?EPatente
    {
        return $this->em->createQueryBuilder()
            ->select('DISTINCT p', 's')
            ->from(EPatente::class, 'p')
            ->leftJoin('p.spese', 's')
            ->where('p.idPa = :idPa')
            ->setParameter('idPa', $idPa)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Recupera tutte le patenti con le spese associate, ordinate per tipo.
     */
    public function findPacchettiPatenti(): array
    {
        return $this->em->createQueryBuilder()
            ->select('DISTINCT p', 's')
            ->from(EPatente::class, 'p')
            ->leftJoin('p.spese', 's')
            ->orderBy('p.tipo', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
