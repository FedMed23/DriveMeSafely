<?php
namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\EPatente;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class FPatente
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * Recupera una patente per ID. Se non esiste, ritorna null.
     */
    public function findById(int $idPa): ?EPatente
    {
        return $this->em->getRepository(EPatente::class)->find($idPa);
    }

    /**
     * Recupera una patente per tipo. Se non esiste, ritorna null.
     */
    public function findByTipo(string $tipo): ?EPatente
    {
        return $this->em->getRepository(EPatente::class)->findOneBy([
            'tipo' => $tipo,
        ]);
    }

    /**
     * Recupera tutte le patenti ordinate per ID.
     */
    public function findAll(): array
    {
        return $this->em->getRepository(EPatente::class)->findBy([], [
            'idPa' => 'ASC',
        ]);
    }

    /**
     * Recupera un singolo pacchetto patente con le sue spese. Se non esiste, ritorna null.
     */
    public function findPacchettoById(int $idPa): ?EPatente
    {
        $patente = $this->findById($idPa);

        if ($patente !== null) {
            $this->loadSpese($patente);
        }

        return $patente;
    }

    /**
     * Recupera tutti i pacchetti patente con le relative spese caricate.
     */
    public function findPacchettiPatenti(): array
    {
        $patenti = $this->em->getRepository(EPatente::class)->findBy([], [
            'tipo' => 'ASC',
        ]);

        foreach ($patenti as $patente) {
            $this->loadSpese($patente);
        }

        return $patenti;
    }

    private function loadSpese(EPatente $patente): void
    {
        $rows = $this->em->getConnection()->fetchAllAssociative(
            'SELECT id_spesa
             FROM patente_has_spesa
             WHERE id_patente = :id_patente
             ORDER BY id_spesa',
            ['id_patente' => $patente->getId()]
        );

        $spese = [];
        foreach ($rows as $row) {
            $spesa = $this->em->getRepository(
                \CamassoMedelago\DriveMeSafely\Entity\ESpesa::class
            )->find((int) $row['id_spesa']);

            if ($spesa !== null) {
                $spese[] = $spesa;
            }
        }

        $patente->setSpese(new ArrayCollection($spese));
    }
}
