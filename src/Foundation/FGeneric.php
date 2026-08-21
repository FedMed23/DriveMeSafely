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

    public function save(object $entity): void
    {
        $this->em->persist($entity);
        $this->em->flush();
    }

    public function update(object $entity): void
    {
        $this->em->flush();
    }

    public function delete(object $entity): void
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    public function findById(int $id): ?object
    {
        return $this->em->find($this->entityClass, $id);
    }

    public function findAll(): array
    {
        return $this->em
            ->getRepository($this->entityClass)
            ->findAll();
    }
}