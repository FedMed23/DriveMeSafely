<?php
// src/Foundation/FDipendente.php

namespace DriveMeSafely\src\Foundation;

use DriveMeSafely\src\Entity\EDipendente;
use Doctrine\ORM\EntityManagerInterface;

class FDipendente
{
    private EntityManagerInterface $em;

    // Costruttore: riceve l'EntityManager (serve per parlare col database)
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // ---------------------- SALVATAGGIO ----------------------
    // Inserisce un nuovo dipendente nel database
    public function save(EDipendente $dipendente): void
    {
        $this->em->persist($dipendente); // prepara il salvataggio
        $this->em->flush();              // esegue davvero la query
    }

    // ---------------------- LETTURA ----------------------
    // Recupera un dipendente tramite ID
    public function getDipendenteById($id): ?EDipendente
    {
        return $this->em->getRepository(EDipendente::class)->find($id);
    }

    // Recupera tutti i dipendenti
    public function getAllDipendenti(): array
    {
        return $this->em->getRepository(EDipendente::class)->findAll();
    }

    // ---------------------- AGGIORNAMENTO ----------------------
    // Aggiorna un dipendente (Doctrine lo fa automaticamente)
    public function update(EDipendente $dipendente): void
    {
        $this->em->flush();
    }

    // ---------------------- ELIMINAZIONE ----------------------
    // Elimina un dipendente
    public function delete(EDipendente $dipendente): void
    {
        $this->em->remove($dipendente);
        $this->em->flush();
    }

    // ---------------------- METODI PERSONALIZZATI ----------------------

    // Trova dipendenti per ruolo (es. "Istruttore")
    public function getDipendentiByRuolo(string $ruolo): array
    {
        return $this->em->getRepository(EDipendente::class)->findBy([
            'ruolo' => $ruolo
        ]);
    }

    // Trova dipendenti con stipendio maggiore di un certo valore
    public function getDipendentiByStipendioMaggioreDi(float $stipendio): array
    {
        return $this->em->getRepository(EDipendente::class)
            ->createQueryBuilder('d')
            ->where('d.stipendio > :stipendio')
            ->setParameter('stipendio', $stipendio)
            ->getQuery()
            ->getResult();
    }
}
?>
