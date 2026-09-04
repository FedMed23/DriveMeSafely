<?php
// src/Foundation/FCartaDiCredito.php
// Classe Foundation di Carta di Credito

namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\ECartaDiCredito;
use CamassoMedelago\DriveMeSafely\Entity\EUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\Utils\CartaDiCreditoUtil;
use Doctrine\ORM\EntityManagerInterface;

class FCartaDiCredito
{
    // ---------------------- COSTRUTTORE ----------------------
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    // ---------------------- SALVATAGGIO ----------------------
    /**
     * Salva una nuova carta di credito.
     */
    public function save(ECartaDiCredito $carta): void
    {
        $this->em->persist($carta);
        $this->em->flush();
    }

    // ---------------------- LETTURA ----------------------
    /**
     * Recupera una carta tramite il numero della carta (calcolando l'hash per la query).
     */
    public function findByNumeroCarta(string $numeroCarta): ?ECartaDiCredito
    {
        $numeroCartaNormalizzato = CartaDiCreditoUtil::normalizzaNumeroCarta($numeroCarta);
        $hash = CartaDiCreditoUtil::hashNumeroCarta($numeroCartaNormalizzato);

        // Ricerca per hash SHA-256
        $carta = $this->findByNumeroCartaHash($hash);

        // Fallback: se non trovata per hash, cerca per numero in chiaro (retrocompatibilità)
        if ($carta === null && $numeroCartaNormalizzato !== '') {
            $carta = $this->em
                ->getRepository(ECartaDiCredito::class)
                ->findOneBy([
                    'numeroCarta' => $numeroCartaNormalizzato
                ]);
        }

        return $carta;
    }

    /**
     * Recupera una carta tramite l'hash del numero della carta.
     */
    public function findByNumeroCartaHash(string $hash): ?ECartaDiCredito
    {
        return $this->em
            ->getRepository(ECartaDiCredito::class)
            ->findOneBy([
                'numeroCarta' => $hash
            ]);
    }

    public function findByNumber(string $numeroCarta): ?ECartaDiCredito
    {
        return $this->findByNumeroCarta($numeroCarta);
    }

    /**
     * Recupera tutte le carte in scadenza
     * entro una determinata data.
     */
    public function findCarteInScadenza(\DateTimeImmutable $data): array
    {
        return $this->em
            ->getRepository(ECartaDiCredito::class)
            ->createQueryBuilder('c')
            ->where('c.dataScadenza <= :data')
            ->setParameter('data', $data)
            ->getQuery()
            ->getResult();
    }

    /**
     * Recupera tutte le carte salvate nel database.
     */
    public function findAllCarte(): array
    {
        return $this->em
            ->getRepository(ECartaDiCredito::class)
            ->createQueryBuilder('c')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recupera tutte le carte utilizzate nei pagamenti
     * effettuati da uno specifico utente.
     */
    public function findByUtenteId(int $idUtente): array
    {
        return $this->em
            ->getRepository(ECartaDiCredito::class)
            ->createQueryBuilder('c')
            ->select('c')
            ->innerJoin(
                'CamassoMedelago\DriveMeSafely\Entity\EPagamento',
                'p',
                'WITH',
                'p.cartaDiCredito = c'
            )
            ->innerJoin(
                'p.utenteRegistrato',
                'u'
            )
            ->where('u.id = :id')
            ->setParameter('id', $idUtente)
            ->getQuery()
            ->getResult();
    }

    /**
     * Recupera tutte le carte utilizzate nei pagamenti
     * effettuati da uno specifico utente.
     */
    public function findByUtente(EUtenteRegistrato $utente): array
    {
        return $this->em
            ->getRepository(ECartaDiCredito::class)
            ->createQueryBuilder('c')
            ->select('c')
            ->innerJoin(
                'CamassoMedelago\DriveMeSafely\Entity\EPagamento',
                'p',
                'WITH',
                'p.cartaDiCredito = c'
            )
            ->where('p.utenteRegistrato = :utente')
            ->setParameter('utente', $utente)
            ->getQuery()
            ->getResult();
    }

    // ---------------------- AGGIORNAMENTO ----------------------
    /**
     * Aggiorna una carta di credito esistente.
     */
    public function update(ECartaDiCredito $carta): void
    {
        $this->em->flush();
    }

    // ---------------------- ELIMINAZIONE ----------------------
    /**
     * Elimina una carta di credito.
     */
    public function delete(ECartaDiCredito $carta): void
    {
        $this->em->remove($carta);
        $this->em->flush();
    }
}
