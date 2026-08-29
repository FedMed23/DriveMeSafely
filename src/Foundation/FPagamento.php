<?php
// src/Foundation/FPagamento.php

namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\EPagamento;
use CamassoMedelago\DriveMeSafely\Entity\EUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\Entity\ESpesa;
use CamassoMedelago\DriveMeSafely\Entity\ECartaDiCredito;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class FPagamento
{
    private EntityManagerInterface $em;

    // ---------------------- COSTRUTTORE ----------------------

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // ---------------------- SALVATAGGIO ----------------------

    /**
     * Salva un nuovo pagamento o aggiorna un pagamento esistente.
     */
    public function save(EPagamento $pagamento): void
    {
        $this->em->persist($pagamento);
        $this->em->flush();
    }

    public function persist(EPagamento $pagamento): void
    {
        $this->em->persist($pagamento);
    }

    public function findByUtenteAndSpesa(
        EUtenteRegistrato $utente,
        ESpesa $spesa
    ): ?EPagamento {
        return $this->em->getRepository(EPagamento::class)
            ->createQueryBuilder('p')
            ->where('p.utenteRegistrato = :utente')
            ->andWhere('p.spesa = :spesa')
            ->andWhere('p.stato = :stato')
            ->setParameter('utente', $utente)
            ->setParameter('spesa', $spesa)
            ->setParameter('stato', 'PAGATO')
            ->orderBy('p.data', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }


    // ---------------------- LETTURA ----------------------

    /**
     * Recupera un pagamento tramite ID.
     */
    public function getPagamentoById(int $id): ?EPagamento
    {
        return $this->em
            ->getRepository(EPagamento::class)
            ->find($id);
    }

    /**
     * Recupera tutti i pagamenti registrati nel sistema,
     * ordinati per ID decrescente.
     */
    public function getAllPagamenti(): array
    {
        return $this->em
            ->getRepository(EPagamento::class)
            ->createQueryBuilder('p')
            ->orderBy('p.idPag', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recupera la cronologia dei pagamenti di un determinato utente,
     * ordinata dalla data più recente.
     */
    public function getPagamentiById(int $id_utente): array
    {
        $utente = $this->em
            ->getRepository(EUtenteRegistrato::class)
            ->find($id_utente);

        if (!$utente) {
            return [];
        }

        return $this->em
            ->getRepository(EPagamento::class)
            ->createQueryBuilder('p')
            ->where('p.utenteRegistrato = :utente')
            ->setParameter('utente', $utente)
            ->orderBy('p.data', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recupera tutti i pagamenti relativi a uno specifico stato.
     *
     * Lo stato viene normalizzato in maiuscolo e senza spazi
     * iniziali/finali, come nel DAO Java.
     */
    public function getPagamentiByStato(string $stato): array
    {
        return $this->em
            ->getRepository(EPagamento::class)
            ->createQueryBuilder('p')
            ->where('p.stato = :stato')
            ->setParameter('stato', strtoupper(trim($stato)))
            ->orderBy('p.data', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recupera i pagamenti associati a una determinata carta di credito.
     */
    public function getPagamentiByCarta(ECartaDiCredito $carta): array
    {
        return $this->em
            ->getRepository(EPagamento::class)
            ->findBy([
                'cartaDiCredito' => $carta
            ]);
    }

    /**
     * Recupera i pagamenti associati a una determinata spesa.
     */
    public function getPagamentiBySpesa(ESpesa $spesa): array
    {
        return $this->em
            ->getRepository(EPagamento::class)
            ->findBy([
                'idSpesa' => $spesa
            ]);
    }

    /**
     * Recupera i pagamenti effettuati dopo una determinata data.
     */
    public function getPagamentiDopoData(DateTimeImmutable $data): array
    {
        return $this->em
            ->getRepository(EPagamento::class)
            ->createQueryBuilder('p')
            ->where('p.data > :data')
            ->setParameter('data', $data)
            ->getQuery()
            ->getResult();
    }

    /**
     * Recupera lo storico dei pagamenti effettivamente pagati,
     * ordinato per data.
     */
    public function findStoricoPagamenti(): array
    {
        return $this->em
            ->getRepository(EPagamento::class)
            ->createQueryBuilder('p')
            ->where('p.stato = :stato')
            ->setParameter('stato', 'PAGATO')
            ->orderBy('p.data', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // ---------------------- VERIFICA PAGAMENTI ----------------------

    /**
     * Verifica se una determinata spesa è stata pagata da un iscritto.
     */
    public function spesaPagataIscritto(
        int $idSpesa,
        int $idIscritto
    ): bool {
        $conteggio = $this->em
            ->getRepository(EPagamento::class)
            ->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->join('p.spesa', 's')
            ->where('s.idSpesa = :idSpesa')
            ->andWhere('p.utenteRegistrato = :idIscritto')
            ->andWhere('p.stato = :stato')
            ->setParameter('idSpesa', $idSpesa)
            ->setParameter('idIscritto', $idIscritto)
            ->setParameter('stato', 'PAGATO')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $conteggio > 0;
    }

    /**
     * Verifica se una spesa destinata al proprietario
     * è stata pagata dal proprietario indicato.
     */
    public function spesaPagataProprietario(
        int $idSpesa,
        int $idProprietario
    ): bool {
        $conteggio = $this->em
            ->getRepository(EPagamento::class)
            ->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->join('p.spesa', 's')
            ->where('s.idSpesa = :idSpesa')
            ->andWhere('s.ambito = :ambito')
            ->andWhere('p.utenteRegistrato = :idProprietario')
            ->andWhere('p.stato = :stato')
            ->setParameter('idSpesa', $idSpesa)
            ->setParameter('ambito', 'PROPRIETARIO')
            ->setParameter('idProprietario', $idProprietario)
            ->setParameter('stato', 'PAGATO')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $conteggio > 0;
    }

    // ---------------------- ENTRATE / USCITE ----------------------

    /**
     * Calcola le entrate giornaliere derivanti dalle spese
     * relative alla patente.
     */
    public function getEntrateGiornaliere(
        \DateTimeInterface $data
    ): float {
        $totale = $this->em
            ->getRepository(EPagamento::class)
            ->createQueryBuilder('p')
            ->select('SUM(s.importo)')
            ->join('p.spesa', 's')
            ->where('p.data = :data')
            ->andWhere('p.stato = :stato')
            ->andWhere('s.ambito = :ambito')
            ->setParameter('data', $data)
            ->setParameter('stato', 'PAGATO')
            ->setParameter('ambito', 'PATENTE')
            ->getQuery()
            ->getSingleScalarResult();

        return $totale !== null ? (float) $totale : 0.0;
    }

    /**
     * Calcola le uscite giornaliere relative alle spese
     * del proprietario.
     */
    public function getUsciteGiornaliere(
        \DateTimeInterface $data
    ): float {
        $totale = $this->em
            ->getRepository(EPagamento::class)
            ->createQueryBuilder('p')
            ->select('SUM(s.importo)')
            ->join('p.spesa', 's')
            ->where('p.data = :data')
            ->andWhere('p.stato = :stato')
            ->andWhere('s.ambito = :ambito')
            ->setParameter('data', $data)
            ->setParameter('stato', 'PAGATO')
            ->setParameter('ambito', 'PROPRIETARIO')
            ->getQuery()
            ->getSingleScalarResult();

        return $totale !== null ? (float) $totale : 0.0;
    }

    /**
     * Calcola il totale pagato da un iscritto.
     */
    public function getTotalePagatoIscritto(
        int $idUtente
    ): float {
        $totale = $this->em
            ->getRepository(EPagamento::class)
            ->createQueryBuilder('p')
            ->select('SUM(s.importo)')
            ->join('p.spesa', 's')
            ->where('p.utenteRegistrato = :idUtente')
            ->setParameter('idUtente', $idUtente)
            ->getQuery()
            ->getSingleScalarResult();

        return $totale !== null ? (float) $totale : 0.0;
    }

    /**
     * Calcola il totale pagato dal proprietario.
     */
    public function getTotalePagatoProprietario(
        int $idProprietario
    ): float {
        $totale = $this->em
            ->getRepository(EPagamento::class)
            ->createQueryBuilder('p')
            ->select('SUM(s.importo)')
            ->join('p.spesa', 's')
            ->where('s.ambito = :ambito')
            ->andWhere('p.utenteRegistrato = :idProprietario')
            ->setParameter('ambito', 'PROPRIETARIO')
            ->setParameter('idProprietario', $idProprietario)
            ->getQuery()
            ->getSingleScalarResult();

        return $totale !== null ? (float) $totale : 0.0;
    }

    /**
     * Restituisce il totale delle entrate pagate dagli iscritti,
     * relative alle spese di ambito PATENTE.
     */
    public function getTotaleEntrate(): float
    {
        $totale = $this->em
            ->getRepository(EPagamento::class)
            ->createQueryBuilder('p')
            ->select('SUM(s.importo)')
            ->join('p.spesa', 's')
            ->where('p.stato = :stato')
            ->andWhere('s.ambito = :ambito')
            ->setParameter('stato', 'PAGATO')
            ->setParameter('ambito', 'PATENTE')
            ->getQuery()
            ->getSingleScalarResult();

        return $totale !== null ? (float) $totale : 0.0;
    }

    /**
     * Restituisce il totale delle spese del proprietario
     * che non risultano ancora pagate.
     */
    public function getTotaleUsciteDaPagare(): float
    {
        $totale = $this->em
            ->getRepository(ESpesa::class)
            ->createQueryBuilder('s')
            ->select('SUM(s.importo)')
            ->where('s.ambito = :ambito')
            ->andWhere(
                'NOT EXISTS (
                    SELECT p2.idPag
                    FROM ' . EPagamento::class . ' p2
                    WHERE p2.idSpesa = s
                    AND p2.stato = :stato
                )'
            )
            ->setParameter('ambito', 'PROPRIETARIO')
            ->setParameter('stato', 'PAGATO')
            ->getQuery()
            ->getSingleScalarResult();

        return $totale !== null ? (float) $totale : 0.0;
    }

    // ---------------------- AGGIORNAMENTO ----------------------

    /**
     * Aggiorna un pagamento già gestito da Doctrine.
     */
    public function update(EPagamento $pagamento): void
    {
        $this->em->flush();
    }

    // ---------------------- ELIMINAZIONE ----------------------

    /**
     * Elimina un pagamento.
     */
    public function delete(EPagamento $pagamento): void
    {
        $this->em->remove($pagamento);
        $this->em->flush();
    }
}
?>
