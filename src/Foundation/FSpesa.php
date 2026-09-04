<?php
namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\ESpesa;
use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use Doctrine\ORM\EntityManagerInterface;

class FSpesa 
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    // ---------------- CREATE / UPDATE / DELETE ----------------

    public function save(ESpesa $spesa): void
    {
        $this->em->persist($spesa);
        $this->em->flush();
    }

    public function delete(ESpesa $spesa): void
    {
        $this->em->remove($spesa);
        $this->em->flush();
    }

    // ---------------- READ (METODI COMPATTI) ----------------

    public function findById(int $id): ?ESpesa
    {
        return $this->em->find(ESpesa::class, $id);
    }

    public function findAll(): array
    {
        return $this->em->createQuery('SELECT s FROM CamassoMedelago\DriveMeSafely\Entity\ESpesa s ORDER BY s.idSpesa DESC')
            ->getResult();
    }

    public function findByTipologia(string $tipologia): array
    {
        return $this->em->createQuery('SELECT s FROM CamassoMedelago\DriveMeSafely\Entity\ESpesa s WHERE LOWER(TRIM(s.tipologia)) = LOWER(TRIM(:tipologia))')
            ->setParameter('tipologia', $tipologia)
            ->getResult();
    }

    public function findSpeseIscritto(int $idIscritto): array
    {
        $iscritto = $this->em->find(EIscritto::class, $idIscritto);
        if ($iscritto === null || $iscritto->getTipoPatente() === null) {
            return [];
        }

        return $iscritto->getTipoPatente()->getSpese()->toArray();
    }

    public function isAssociataAIscritto(int $idIscritto, int $idSpesa): bool
    {
        return (int) $this->em->createQuery(
            'SELECT COUNT(s.idSpesa)
             FROM CamassoMedelago\DriveMeSafely\Entity\EIscritto i
             JOIN i.tipoPatente p
             JOIN p.spese s
             WHERE i.id = :utente AND s.idSpesa = :spesa'
        )->setParameter('utente', $idIscritto)
            ->setParameter('spesa', $idSpesa)
            ->getSingleScalarResult() > 0;
    }

    public function findSpeseProprietario(): array
    {
        return $this->em->createQuery("SELECT s FROM CamassoMedelago\DriveMeSafely\Entity\ESpesa s WHERE s.ambito = 'PROPRIETARIO' ORDER BY s.idSpesa DESC")
            ->getResult();
    }

    public function findSpesePatenti(): array
    {
        return $this->em->createQuery("SELECT s FROM CamassoMedelago\DriveMeSafely\Entity\ESpesa s WHERE s.ambito = 'PATENTE' ORDER BY s.idSpesa DESC")
            ->getResult();
    }

    // ---------------- METODI DI AGGREGAZIONE (TOTALI) ----------------

    public function getTotaleSpeseGenerale(): float
    {
        return (float) $this->em->createQuery('SELECT SUM(s.importo) FROM CamassoMedelago\DriveMeSafely\Entity\ESpesa s')
            ->getSingleScalarResult() ?? 0.0;
    }
    
    public function getTotaleSpeseByTipo(string $tipo): float
    {
       
        return (float) $this->em->createQuery('SELECT SUM(s.importo) FROM CamassoMedelago\DriveMeSafely\Entity\ESpesa s WHERE LOWER(TRIM(s.tipologia)) = LOWER(TRIM(:tipo))')
            ->setParameter('tipo', $tipo)
            ->getSingleScalarResult() ?? 0.0;
    }

    public function getTotaleSpeseProprietario(): float
    {
        return (float) $this->em->createQuery("SELECT SUM(s.importo) FROM CamassoMedelago\DriveMeSafely\Entity\ESpesa s WHERE s.ambito = 'PROPRIETARIO'")
            ->getSingleScalarResult() ?? 0.0;
    }

    public function getTotaleSpesePatente(): float
    {
        return (float) $this->em->createQuery("SELECT SUM(s.importo) FROM CamassoMedelago\DriveMeSafely\Entity\ESpesa s WHERE s.ambito = 'PATENTE'")
            ->getSingleScalarResult() ?? 0.0;
    }
    
    public function getTotaleSpeseIscritto(int $idIscritto): float
    {
        return (float) $this->em->createQuery('SELECT SUM(s.importo) FROM CamassoMedelago\DriveMeSafely\Entity\EIscritto i JOIN i.tipoPatente p JOIN p.spese s WHERE i.id = :idIscritto')
            ->setParameter('idIscritto', $idIscritto)
            ->getSingleScalarResult() ?? 0.0;
    }
    
    public function getTotaleDaIncassare(): float
    {
        return (float) $this->em->createQuery(
            "SELECT SUM(s.importo) 
             FROM CamassoMedelago\DriveMeSafely\Entity\EIscritto i 
             JOIN i.tipoPatente p 
             JOIN p.spese s 
             WHERE s.ambito = 'PATENTE' 
             AND NOT EXISTS (
                 SELECT pag FROM CamassoMedelago\DriveMeSafely\Entity\EPagamento pag 
                 WHERE pag.utenteRegistrato = i 
                 AND pag.spesa = s 
                 AND pag.stato = 'PAGATO'
             )"
        )->getSingleScalarResult() ?? 0.0;
    }

    // ---------------- QUERY CON ARRAY DI OGGETTI (RELAZIONI COMPLESSE) ----------------
    
    public function findSpeseConPagamentoIscritto(int $idIscritto): array
    {
        return $this->em->createQuery(
            'SELECT s, pag 
             FROM CamassoMedelago\DriveMeSafely\Entity\EIscritto i 
             JOIN i.tipoPatente pat 
             JOIN pat.spese s 
             LEFT JOIN CamassoMedelago\DriveMeSafely\Entity\EPagamento pag WITH pag.spesa = s AND pag.utenteRegistrato = i
             WHERE i.id = :idUtente'
        )->setParameter('idUtente', $idIscritto)
         ->getResult();
    }
    
    public function findSpeseConPagamentoProprietario(int $idProprietario): array
    {
        return $this->em->createQuery(
            "SELECT s, p 
             FROM CamassoMedelago\DriveMeSafely\Entity\ESpesa s 
             LEFT JOIN CamassoMedelago\DriveMeSafely\Entity\EPagamento p WITH p.spesa = s AND p.utenteRegistrato = :idUtente 
             WHERE s.ambito = 'PROPRIETARIO'"
        )->setParameter('idUtente', $idProprietario)
         ->getResult();
    }
}
