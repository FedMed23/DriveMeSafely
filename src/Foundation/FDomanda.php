<?php
// src/Foundation/FDomanda.php

namespace DriveMeSafely\src\Foundation;

use DriveMeSafely\src\Entity\EDomanda;
use Doctrine\ORM\EntityManagerInterface;

class FDomanda
{
    private EntityManagerInterface $em;

    // Costruttore: serve per collegarsi al database
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // ---------------------- SALVATAGGIO ----------------------
    // Salva una nuova domanda
    public function save(EDomanda $domanda): void
    {
        $this->em->persist($domanda);
        $this->em->flush();
    }

    // ---------------------- LETTURA ----------------------
    // Recupera una domanda tramite ID
    public function getDomandaById(int $id): ?EDomanda
    {
        return $this->em->getRepository(EDomanda::class)->find($id);
    }

    // Recupera tutte le domande
    public function getAllDomande(): array
    {
        return $this->em->getRepository(EDomanda::class)->findAll();
    }

    // ---------------------- AGGIORNAMENTO ----------------------
    // Aggiorna una domanda
    public function update(EDomanda $domanda): void
    {
        $this->em->flush();
    }

    // ---------------------- ELIMINAZIONE ----------------------
    // Elimina una domanda
    public function delete(EDomanda $domanda): void
    {
        $this->em->remove($domanda);
        $this->em->flush();
    }

    // ---------------------- METODI PERSONALIZZATI ----------------------

    // Trova tutte le domande con risposta corretta TRUE
    public function getDomandeCorrette(): array
    {
        return $this->em->getRepository(EDomanda::class)->findBy([
            'rispostaCorretta' => true
        ]);
    }

    // Trova tutte le domande con risposta FALSE
    public function getDomandeErrate(): array
    {
        return $this->em->getRepository(EDomanda::class)->findBy([
            'rispostaCorretta' => false
        ]);
    }

    // Cerca domande per parola nel contenuto (utile per quiz)
    public function cercaPerContenuto(string $testo): array
    {
        return $this->em->getRepository(EDomanda::class)
            ->createQueryBuilder('d')
            ->where('d.contenuto LIKE :testo')
            ->setParameter('testo', '%' . $testo . '%')
            ->getQuery()
            ->getResult();
    }
}
?>
