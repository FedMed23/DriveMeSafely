<?php

namespace CamassoMedelago\DriveMeSafely\Foundation;

use Doctrine\ORM\EntityManagerInterface;

class FGeneric
{
    protected EntityManagerInterface $em;
    protected string $entityClass;

    public function __construct(
        EntityManagerInterface $em,
        string $entityClass
    ) {
        $this->em = $em;
        $this->entityClass = $entityClass;
    }

    /**
     * Inserisce una nuova entità nel contesto di persistenza.
     */
    public function save(object $entity): void
    {
        $this->em->persist($entity);
    }

    /**
     * Aggiorna lo stato di un'entità esistente.
     *
     * Doctrine gestisce automaticamente l'entità se è già
     * presente nel contesto di persistenza.
     */
    public function update(object $entity): void
    {
        $this->em->persist($entity);
    }

    /**
     * Rimuove un'entità dal database.
     */
    public function delete(object $entity): void
    {
        if (!$this->em->contains($entity)) {
            $entity = $this->em->merge($entity);
        }

        $this->em->remove($entity);
    }

    /**
     * Cerca un'entità tramite la sua chiave primaria.
     */
    public function findById(int $id): ?object
    {
        return $this->em->find($this->entityClass, $id);
    }

    /**
     * Recupera tutte le entità della classe specificata.
     */
    public function findAll(): array
    {
        return $this->em
            ->getRepository($this->entityClass)
            ->findAll();
    }

    /**
     * Esegue una query DQL/JPQL generica.
     *
     * Esempio:
     * $foundation->findAllByQuery(
     *     'SELECT e FROM EDomanda e ORDER BY e.idDomanda ASC'
     * );
     */
    public function findAllByQuery(string $dql): array
    {
        return $this->em
            ->createQuery($dql)
            ->getResult();
    }
}
