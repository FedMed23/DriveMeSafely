<?php

namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\EUtenteRegistrato;
use Doctrine\ORM\EntityManagerInterface;

class FUtente
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // ------------------- SAVE -------------------

    public function save(EUtenteRegistrato $utente): void
    {
        $this->em->persist($utente);
        $this->em->flush();
    }

    // ------------------- GET BY ID -------------------

    public function getById(int $id): ?EUtenteRegistrato
    {
        return $this->em
            ->getRepository(EUtenteRegistrato::class)
            ->find($id);
    }

    // ------------------- GET BY EMAIL -------------------

    public function getByEmail(string $email): ?EUtenteRegistrato
    {
        return $this->em
            ->getRepository(EUtenteRegistrato::class)
            ->findOneBy([
                'email' => $email
            ]);
    }

    // ------------------- GET BY USERNAME -------------------

    public function getByUsername(string $username): ?EUtenteRegistrato
    {
        return $this->em
            ->getRepository(EUtenteRegistrato::class)
            ->findOneBy([
                'username' => $username
            ]);
    }

    // ------------------- GET ALL -------------------

    /**
     * Restituisce tutti gli utenti registrati ordinati
     * per cognome e successivamente per nome.
     *
     * Equivalente a:
     * SELECT u FROM UtenteRegistrato u
     * ORDER BY u.cognome, u.nome
     *
     * @return EUtenteRegistrato[]
     */
    public function findAll(): array
    {
        return $this->em
            ->getRepository(EUtenteRegistrato::class)
            ->createQueryBuilder('u')
            ->orderBy('u.cognome', 'ASC')
            ->addOrderBy('u.nome', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // ------------------- DELETE -------------------

    public function delete(EUtenteRegistrato $utente): void
    {
        $this->em->remove($utente);
        $this->em->flush();
    }
}
?>
