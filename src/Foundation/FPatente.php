<?php
// src/Foundation/FPatente.php

namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\EPatente;
use Doctrine\ORM\EntityManagerInterface;

class FPatente
{
    private EntityManagerInterface $em;

    // Costruttore
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // ---------------------- SALVATAGGIO ----------------------
    public function save(EPatente $patente): void
    {
        $this->em->persist($patente);
        $this->em->flush();
    }

    // ---------------------- LETTURA ----------------------
    // Recupera per ID
    public function getPatenteById(int $id): ?EPatente
    {
        return $this->em->getRepository(EPatente::class)->find($id);
    }

    // Recupera tutte le patenti
    public function getAllPatenti(): array
    {
        return $this->em->getRepository(EPatente::class)->findAll();
    }

    // ---------------------- AGGIORNAMENTO ----------------------
    public function update(EPatente $patente): void
    {
        $this->em->flush();
    }

    // ---------------------- ELIMINAZIONE ----------------------
    public function delete(EPatente $patente): void
    {
        $this->em->remove($patente);
        $this->em->flush();
    }

    // ---------------------- METODI PERSONALIZZATI ----------------------

    // Trova patente per tipo (A, B, C...)
    public function getByTipo(string $tipo): ?EPatente
    {
        return $this->em->getRepository(EPatente::class)->findOneBy([
            'tipo' => $tipo
        ]);
    }
}
?>
