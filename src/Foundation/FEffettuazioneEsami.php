<?php
// src/Foundation/FEffettuazioneEsami.php

namespace DriveMeSafely\src\Foundation;

use DriveMeSafely\src\Entity\EEffettuazioneEsami;
use Doctrine\ORM\EntityManagerInterface;

class FEffettuazioneEsami
{
    private EntityManagerInterface $em;

    // Costruttore: collega Doctrine al database
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // ---------------------- SALVATAGGIO ----------------------
    // Salva una nuova effettuazione di esame
    public function save(EEffettuazioneEsami $effettuazione): void
    {
        $this->em->persist($effettuazione);
        $this->em->flush();
    }

    // ---------------------- LETTURA ----------------------
    // Recupera per ID
    public function getEffettuazioneById(int $id): ?EEffettuazioneEsami
    {
        return $this->em->getRepository(EEffettuazioneEsami::class)->find($id);
    }

    // Recupera tutte
    public function getAllEffettuazioni(): array
    {
        return $this->em->getRepository(EEffettuazioneEsami::class)->findAll();
    }

    // ---------------------- AGGIORNAMENTO ----------------------
    public function update(EEffettuazioneEsami $effettuazione): void
    {
        $this->em->flush();
    }

    // ---------------------- ELIMINAZIONE ----------------------
    public function delete(EEffettuazioneEsami $effettuazione): void
    {
        $this->em->remove($effettuazione);
        $this->em->flush();
    }

    // ---------------------- METODI PERSONALIZZATI ----------------------

    // Trova tutte le effettuazioni di un iscritto
    public function getByIdIscritto(int $idIscritto): array
    {
        return $this->em->getRepository(EEffettuazioneEsami::class)->findBy([
            'idIscritto' => $idIscritto
        ]);
    }

    // Trova tutte le effettuazioni di un esame specifico
    public function getByEsame($esame): array
    {
        return $this->em->getRepository(EEffettuazioneEsami::class)->findBy([
            'esame' => $esame
        ]);
    }

    // Trova tutti gli esami superati
    public function getEsamiSuperati(): array
    {
        return $this->em->getRepository(EEffettuazioneEsami::class)->findBy([
            'superato' => true
        ]);
    }

    // Trova tutti gli esami NON superati
    public function getEsamiNonSuperati(): array
    {
        return $this->em->getRepository(EEffettuazioneEsami::class)->findBy([
            'superato' => false
        ]);
    }

    // Trova chi ha fatto più di X tentativi
    public function getByTentativiMaggioriDi(int $tentativi): array
    {
        return $this->em->getRepository(EEffettuazioneEsami::class)
            ->createQueryBuilder('e')
            ->where('e.tentativi > :tentativi')
            ->setParameter('tentativi', $tentativi)
            ->getQuery()
            ->getResult();
    }
}
?>
