<?php
namespace DriveMeSafely\src\Foundation;

use DriveMeSafely\src\Entity\EProprietario;
use Doctrine\ORM\EntityManagerInterface;

class FProprietario
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // ---------------- CREATE / UPDATE ----------------
    /**
     * Salva un proprietario nuovo o aggiornato
     */
    public function save(EProprietario $proprietario): void
    {
        $this->em->persist($proprietario);
        $this->em->flush();
    }

    // ---------------- READ ----------------
    /**
     * Recupera un proprietario per ID
     */
    public function findById(int $id): ?EProprietario
    {
        return $this->em->getRepository(EProprietario::class)->find($id);
    }

    /**
     * Recupera tutti i proprietari
     */
    public function findAll(): array
    {
        return $this->em->getRepository(EProprietario::class)->findAll();
    }

    /**
     * Recupera un proprietario per username
     */
    public function findByUsername(string $username): ?EProprietario
    {
        return $this->em->getRepository(EProprietario::class)->findOneBy([
            'username' => $username
        ]);
    }

    // ---------------- DELETE ----------------
    /**
     * Elimina un proprietario
     */
    public function delete(EProprietario $proprietario): void
    {
        $this->em->remove($proprietario);
        $this->em->flush();
    }
}
?>
