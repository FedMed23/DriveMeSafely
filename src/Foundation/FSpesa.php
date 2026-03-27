<?php
namespace DriveMeSafely\src\Foundation;

use DriveMeSafely\src\Entity\ESpesa;
use Doctrine\ORM\EntityManagerInterface;

class FSpesa
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // ---------------- CREATE / UPDATE ----------------
    /**
     * Salva una spesa nuova o aggiornata
     */
    public function save(ESpesa $spesa): void
    {
        $this->em->persist($spesa);
        $this->em->flush();
    }

    // ---------------- READ ----------------
    /**
     * Recupera una spesa per ID
     */
    public function findById(int $id): ?ESpesa
    {
        return $this->em->getRepository(ESpesa::class)->find($id);
    }

    /**
     * Recupera tutte le spese
     */
    public function findAll(): array
    {
        return $this->em->getRepository(ESpesa::class)->findAll();
    }

    /**
     * Recupera spese per tipologia
     */
    public function findByTipologia(string $tipologia): array
    {
        return $this->em->getRepository(ESpesa::class)->findBy([
            'tipologia' => $tipologia
        ]);
    }

    /**
     * Recupera spese per importo
     */
    public function findByImporto(float $importo): array
    {
        return $this->em->getRepository(ESpesa::class)->findBy([
            'importo' => $importo
        ]);
    }

    // ---------------- DELETE ----------------
    /**
     * Elimina una spesa
     */
    public function delete(ESpesa $spesa): void
    {
        $this->em->remove($spesa);
        $this->em->flush();
    }
}
?>
