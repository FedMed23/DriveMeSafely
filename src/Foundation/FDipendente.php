<?php
// src/Foundation/FDipendente.php

namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\EDipendente;
use Doctrine\ORM\EntityManagerInterface;

class FDipendente
{
    private EntityManagerInterface $em;

    // ---------------------- COSTRUTTORE ----------------------
    /**
     * Costruttore: collega Doctrine al database
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // ---------------------- SALVATAGGIO ----------------------
    /**
     * Inserisce un nuovo dipendente nel database
     */
    public function save(EDipendente $dipendente): void
    {
        $this->em->persist($dipendente);
        $this->em->flush();
    }

    // ---------------------- LETTURA ----------------------
    /**
     * Recupera un dipendente tramite ID
     */
    public function findById(int $id): ?EDipendente
    {
        return $this->em->find(EDipendente::class, $id);
    }

    /**
     * Recupera tutti i dipendenti ordinati
     * alfabeticamente per cognome e nome.
     */
    public function findAllOrdinati(): array
    {
        return $this->em
            ->getRepository(EDipendente::class)
            ->createQueryBuilder('d')
            ->orderBy('d.cognome', 'ASC')
            ->addOrderBy('d.nome', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recupera i dipendenti in base al ruolo.
     *
     * Il confronto non distingue tra maiuscole e minuscole.
     */
    public function findByRuolo(string $ruoloCercato): array
    {
        if (trim($ruoloCercato) === '') {
            return [];
        }

        return $this->em
            ->getRepository(EDipendente::class)
            ->createQueryBuilder('d')
            ->where('UPPER(d.ruolo) = UPPER(:ruolo)')
            ->setParameter('ruolo', trim($ruoloCercato))
            ->orderBy('d.cognome', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recupera un dipendente tramite username.
     *
     * Restituisce null se lo username non esiste.
     */
    public function findByUsername(string $username): ?EDipendente
    {
        if (trim($username) === '') {
            return null;
        }

        return $this->em
            ->getRepository(EDipendente::class)
            ->findOneBy([
                'username' => trim($username)
            ]);
    }

    // ---------------------- AGGIORNAMENTO ----------------------
    /**
     * Aggiorna un dipendente esistente
     */
    public function update(EDipendente $dipendente): void
    {
        $this->em->flush();
    }

    // ---------------------- ELIMINAZIONE ----------------------
    /**
     * Elimina un dipendente
     */
    public function delete(EDipendente $dipendente): void
    {
        $this->em->remove($dipendente);
        $this->em->flush();
    }
}
?>
