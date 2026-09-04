<?php
// src/Foundation/FEffettuazioneEsami.php

namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\EEffettuazioneEsami;
use CamassoMedelago\DriveMeSafely\Entity\EPrenotazioneEsami;
use Doctrine\ORM\EntityManagerInterface;

class FEffettuazioneEsami
{
    // Costruttore: collega Doctrine al database
    public function __construct(private readonly EntityManagerInterface $em)
    {
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

    // Trova tutte le effettuazioni di una prenotazione esame specifica
    public function getByPrenotazioneEsame(EPrenotazioneEsami $prenotazioneEsame): array
    {
        return $this->em->getRepository(EEffettuazioneEsami::class)->findBy([
            'prenotazioneEsame' => $prenotazioneEsame
        ]);
    }

    // Trova tutte le effettuazioni di un iscritto (tramite la prenotazione da cui derivano)
    public function getByIdIscritto(int $idIscritto): array
    {
        return $this->em
            ->getRepository(EEffettuazioneEsami::class)
            ->createQueryBuilder('e')
            ->join('e.prenotazioneEsame', 'p')
            ->where('IDENTITY(p.allievo) = :idIscritto')
            ->setParameter('idIscritto', $idIscritto)
            ->getQuery()
            ->getResult();
    }

    // Trova tutte le effettuazioni di un esame specifico (tramite la prenotazione da cui derivano)
    public function getByEsame($esame): array
    {
        return $this->em
            ->getRepository(EEffettuazioneEsami::class)
            ->createQueryBuilder('e')
            ->join('e.prenotazioneEsame', 'p')
            ->where('p.esame = :esame')
            ->setParameter('esame', $esame)
            ->getQuery()
            ->getResult();
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
