<?php
// src/Foundation/FPagamento.php

namespace DriveMeSafely\src\Foundation;

use DriveMeSafely\src\Entity\EPagamento;
use DriveMeSafely\src\Entity\EUtenteRegistrato;
use DriveMeSafely\src\Entity\ESpesa;
use DriveMeSafely\src\Entity\ECartaDiCredito;
use Doctrine\ORM\EntityManagerInterface;

class FPagamento
{
    private EntityManagerInterface $em;

    // Costruttore
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // ---------------------- SALVATAGGIO ----------------------
    public function save(EPagamento $pagamento): void
    {
        $this->em->persist($pagamento);
        $this->em->flush();
    }

    // ---------------------- LETTURA ----------------------
    // Recupera per ID
    public function getPagamentoById(int $id): ?EPagamento
    {
        return $this->em->getRepository(EPagamento::class)->find($id);
    }

    // Recupera tutti i pagamenti
    public function getAllPagamenti(): array
    {
        return $this->em->getRepository(EPagamento::class)->findAll();
    }

    // ---------------------- AGGIORNAMENTO ----------------------
    public function update(EPagamento $pagamento): void
    {
        $this->em->flush();
    }

    // ---------------------- ELIMINAZIONE ----------------------
    public function delete(EPagamento $pagamento): void
    {
        $this->em->remove($pagamento);
        $this->em->flush();
    }

    // ---------------------- METODI PERSONALIZZATI ----------------------

    // Trova pagamenti di un utente
    public function getPagamentiByUtente(EUtenteRegistrato $utente): array
    {
        return $this->em->getRepository(EPagamento::class)->findBy([
            'idUtenteRegistrato' => $utente
        ]);
    }

    // Trova pagamenti per stato (completato, in attesa, fallito)
    public function getPagamentiByStato(string $stato): array
    {
        return $this->em->getRepository(EPagamento::class)->findBy([
            'stato' => $stato
        ]);
    }

    // Trova pagamenti per carta di credito
    public function getPagamentiByCarta(ECartaDiCredito $carta): array
    {
        return $this->em->getRepository(EPagamento::class)->findBy([
            'cartaDiCredito' => $carta
        ]);
    }

    // Trova pagamenti per spesa
    public function getPagamentiBySpesa(ESpesa $spesa): array
    {
        return $this->em->getRepository(EPagamento::class)->findBy([
            'idSpesa' => $spesa
        ]);
    }

    // Pagamenti dopo una certa data
    public function getPagamentiDopoData(\DateTimeImmutable $data): array
    {
        return $this->em->getRepository(EPagamento::class)
            ->createQueryBuilder('p')
            ->where('p.data > :data')
            ->setParameter('data', $data)
            ->getQuery()
            ->getResult();
    }
}
?>
