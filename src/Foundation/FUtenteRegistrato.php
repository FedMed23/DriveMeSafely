<?php

namespace DriveMeSafely\src\Foundation;

use DriveMeSafely\src\Entity\EUtenteRegistrato;
use Doctrine\ORM\EntityManagerInterface;

class FUtente
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function save(EUtenteRegistrato $utente): void
    {
        $this->em->persist($utente);
        $this->em->flush();
    }

    public function getById(int $id): ?EUtenteRegistrato
    {
        return $this->em->getRepository(EUtenteRegistrato::class)->find($id);
    }

    public function getByEmail(string $email): ?EUtenteRegistrato
    {
        return $this->em->getRepository(EUtenteRegistrato::class)->findOneBy([
            'email' => $email
        ]);
    }

    public function getByUsername(string $username): ?EUtenteRegistrato
    {
        return $this->em->getRepository(EUtenteRegistrato::class)->findOneBy([
            'username' => $username
        ]);
    }

    public function delete(EUtenteRegistrato $utente): void
    {
        $this->em->remove($utente);
        $this->em->flush();
    }
}
?>
