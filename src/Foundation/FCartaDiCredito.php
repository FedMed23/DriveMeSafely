<?php
// src/Foundation/FCartaDiCredito.php
//Classe Foundation di Carta di Credito

namespace DriveMeSafely\src\Foundation;
use DriveMeSafely\src\Entity\ECartaDiCredito;
use Doctrine\ORM\EntityManagerInterface;

class FCartaDiCredito
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // Inserimento / Salvataggio
    public function save(ECartaDiCredito $carta): void
    {
        $this->em->persist($carta);
        $this->em->flush();
    }

    // Recupera carta per numero (chiave primaria)
    public function getCartaPerNumero(string $numeroCarta): ?ECartaDiCredito
    {
        return $this->em->getRepository(ECartaDiCredito::class)->find($numeroCarta);
    }

    // Aggiorna la carta (può includere logica extra, ma il persist non serve se l'entity è già gestita)
    public function update(ECartaDiCredito $carta): void
    {
        $this->em->flush();
    }

    // Elimina carta
    public function delete(ECartaDiCredito $carta): void
    {
        $this->em->remove($carta);
        $this->em->flush();
    }

    // Logica personalizzata: ad esempio trova tutte le carte di un titolare
    public function getCarteDiTitolare(string $nome, string $cognome): array
    {
        return $this->em->getRepository(ECartaDiCredito::class)->findBy([
            'nomeTitolare' => $nome,
            'cognomeTitolare' => $cognome
        ]);
    }
}
?>
