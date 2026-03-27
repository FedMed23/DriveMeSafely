<?php
// src/Foundation/FIscritto.php

namespace DriveMeSafely\src\Foundation;

use DriveMeSafely\src\Entity\EIscritto;
use DriveMeSafely\src\Entity\EPatente;
use Doctrine\ORM\EntityManagerInterface;

class FIscritto
{
    private EntityManagerInterface $em;

    // Costruttore
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // ---------------------- SALVATAGGIO ----------------------
    public function save(EIscritto $iscritto): void
    {
        $this->em->persist($iscritto);
        $this->em->flush();
    }

    // ---------------------- LETTURA ----------------------
    // Recupera per ID
    public function getIscrittoById(int $id): ?EIscritto
    {
        return $this->em->getRepository(EIscritto::class)->find($id);
    }

    // Recupera tutti gli iscritti
    public function getAllIscritti(): array
    {
        return $this->em->getRepository(EIscritto::class)->findAll();
    }

    // ---------------------- AGGIORNAMENTO ----------------------
    public function update(EIscritto $iscritto): void
    {
        $this->em->flush();
    }

    // ---------------------- ELIMINAZIONE ----------------------
    public function delete(EIscritto $iscritto): void
    {
        $this->em->remove($iscritto);
        $this->em->flush();
    }

    // ---------------------- METODI PERSONALIZZATI ----------------------

    // Trova per codice fiscale (molto importante!)
    public function getByCodiceFiscale(string $cf): ?EIscritto
    {
        return $this->em->getRepository(EIscritto::class)->findOneBy([
            'codiceFiscale' => $cf
        ]);
    }

    // Trova iscritti per tipo di patente
    public function getByPatente(EPatente $patente): array
    {
        return $this->em->getRepository(EIscritto::class)->findBy([
            'tipoPatente' => $patente
        ]);
    }

    // Cerca per cognome
    public function getByCognome(string $cognome): array
    {
        return $this->em->getRepository(EIscritto::class)
            ->createQueryBuilder('i')
            ->where('i.cognome LIKE :cognome')
            ->setParameter('cognome', '%' . $cognome . '%')
            ->getQuery()
            ->getResult();
    }
}
?>
