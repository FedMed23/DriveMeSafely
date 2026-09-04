<?php
namespace CamassoMedelago\DriveMeSafely\Foundation;
use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use Doctrine\ORM\EntityManagerInterface;
class FIscritto
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function save(EIscritto $iscritto): void
    {
        $this->em->persist($iscritto);
    }

    public function findById(int $id): ?EIscritto
    {
        return $this->em->find(EIscritto::class, $id);
    }
    public function findByUsername(string $username): ?EIscritto
    {
        $utenti = $this->em->getRepository(EIscritto::class)->findBy([
            'username' => $username
        ]);
        return empty($utenti) ? null : $utenti[0];
    }
    public function findByEmail(string $email): ?EIscritto
    {
        $utenti = $this->em->getRepository(EIscritto::class)->findBy([
            'email' => $email
        ]);
        return empty($utenti) ? null : $utenti[0];
    }
    public function findAttivi(): array
    {
        return $this->em->getRepository(EIscritto::class)->findBy([
            'stato' => true
        ]);
    }
    public function findByCF(string $cf): ?EIscritto
    {
        return $this->em->getRepository(EIscritto::class)->findOneBy([
            'codiceFiscale' => $cf
        ]);
    }
    public function existsByCF(string $cf): bool
    {
        $utenti = $this->em->getRepository(EIscritto::class)->findBy([
            'codiceFiscale' => $cf
        ]);
        return count($utenti) > 0;
    }
}
