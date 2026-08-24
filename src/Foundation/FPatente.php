<?php
namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\EPatente;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;

class FPatente 
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Recupera una patente per ID. Se non esiste, ritorna null.
     */
    public function findById(int $idPa): ?EPatente
    {
            return $this->em->createQuery(
                'SELECT p 
                 FROM CamassoMedelago\DriveMeSafely\Entity\EPatente p 
                 WHERE p.idPa = :idPa'
            )->setParameter('idPa', $idPa)
             ->getOneOrNullResult(); 
    }

    /**
     * Recupera una patente per tipo. Se non esiste, ritorna null.
     */
    public function findByTipo(string $tipo): ?EPatente
    {
            return $this->em->createQuery(
                'SELECT p 
                 FROM CamassoMedelago\DriveMeSafely\Entity\EPatente p 
                 WHERE p.tipo = :tipo'
            )->setParameter('tipo', $tipo)
             ->getOneOrNullResult();
    }

    /**
     * Recupera un singolo pacchetto patente con le sue spese. Se non esiste, ritorna null.
     */
    public function findPacchettoById(int $idPa): ?EPatente
    {
            return $this->em->createQuery(
                'SELECT DISTINCT p, s 
                 FROM CamassoMedelago\DriveMeSafely\Entity\EPatente p 
                 LEFT JOIN p.spese s 
                 WHERE p.idPa = :idPa'
            )->setParameter('idPa', $idPa)
             ->getOneOrNullResult();
    }
        public function findPacchettiPatenti(): array
        {
            return $this->em->createQuery(
                'SELECT DISTINCT p, s 
                 FROM CamassoMedelago\DriveMeSafely\Entity\EPatente p
                 LEFT JOIN p.spese s
                 ORDER BY p.tipo ASC'
            )->getResult();
        }
}
