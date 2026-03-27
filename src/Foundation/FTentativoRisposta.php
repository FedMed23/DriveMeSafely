<?php
// src/Foundation/FTentativoRisposta.php
// Classe Foundation / Service per ETentativoRisposta

namespace DriveMeSafely\src\Foundation;
use DriveMeSafely\src\Entity\ETentativoRisposta;
use Doctrine\ORM\EntityManagerInterface;

class FTentativoRisposta
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // Inserimento / Salvataggio
    public function save(ETentativoRisposta $tentativo): void
    {
        $this->em->persist($tentativo);
        $this->em->flush();
    }

    // Recupera tentativo per ID
    public function getTentativoPerId(int $id): ?ETentativoRisposta
    {
        return $this->em->getRepository(ETentativoRisposta::class)->find($id);
    }

    // Aggiorna tentativo
    public function update(ETentativoRisposta $tentativo): void
    {
        $this->em->flush();
    }

    // Elimina tentativo
    public function delete(ETentativoRisposta $tentativo): void
    {
        $this->em->remove($tentativo);
        $this->em->flush();
    }

    // Recupera tutti i tentativi di uno svolgimento
    public function getTentativiPerSvolgimento(int $svolgimentoId): array
    {
        return $this->em->getRepository(ETentativoRisposta::class)->findBy([
            'svolgimento' => $svolgimentoId
        ]);
    }

    // Recupera tutti i tentativi relativi ad una domanda
    public function getTentativiPerDomanda(int $domandaId): array
    {
        return $this->em->getRepository(ETentativoRisposta::class)->findBy([
            'domanda' => $domandaId
        ]);
    }
}
?>
