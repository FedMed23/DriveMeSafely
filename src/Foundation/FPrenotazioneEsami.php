<?php

namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\EPrenotazioneEsami;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class FPrenotazioneEsami
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // ---------------- CREATE / UPDATE ----------------

    /**
     * Salva una nuova prenotazione o aggiorna una esistente.
     */
    public function save(EPrenotazioneEsami $prenotazione): void
    {
        $this->em->persist($prenotazione);
        $this->em->flush();
    }

    // ---------------- READ ----------------

    /**
     * Recupera una prenotazione tramite ID.
     */
    public function findById(int $id): ?EPrenotazioneEsami
    {
        return $this->em
            ->getRepository(EPrenotazioneEsami::class)
            ->find($id);
    }

    /**
     * Recupera tutte le prenotazioni ordinate per data dell'esame.
     */
    public function findAll(): array
    {
        return $this->em
            ->getRepository(EPrenotazioneEsami::class)
            ->createQueryBuilder('p')
            ->join('p.esame', 'e')
            ->orderBy('e.dataEs', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recupera tutte le prenotazioni di un iscritto,
     * ordinate cronologicamente in base alla data dell'esame.
     */
    public function findByIscritto(int $idIscritto): array
    {
        return $this->em
            ->getRepository(EPrenotazioneEsami::class)
            ->createQueryBuilder('p')
            ->join('p.esame', 'e')
            ->where('IDENTITY(p.allievo) = :idIscritto')
            ->setParameter('idIscritto', $idIscritto)
            ->orderBy('e.dataEs', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recupera lo storico delle prenotazioni di un iscritto,
     * considerando solamente quelle precedenti alla data/ora corrente.
     */
    public function findStoricoByIscritto(int $idIscritto): array
    {
        return $this->em
            ->getRepository(EPrenotazioneEsami::class)
            ->createQueryBuilder('p')
            ->join('p.esame', 'e')
            ->where('IDENTITY(p.allievo) = :idIscritto')
            ->andWhere('p.dataPrenotazione < CURRENT_TIMESTAMP()')
            ->setParameter('idIscritto', $idIscritto)
            ->orderBy('e.dataEs', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Conta quante volte un determinato allievo ha già sostenuto
     * o è stato prenotato per un esame della stessa tipologia.
     *
     * @param int $idIscritto
     * @param string $tipologia
     * @return int
     */
    public function contaTentativiPrecedenti(
        int $idIscritto,
        string $tipologia
    ): int {
        $conteggio = $this->em
            ->getRepository(EPrenotazioneEsami::class)
            ->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->join('p.esame', 'e')
            ->where('IDENTITY(p.allievo) = :idIscritto')
            ->andWhere('e.tipologia = :tipologia')
            ->setParameter('idIscritto', $idIscritto)
            ->setParameter('tipologia', $tipologia)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $conteggio;
    }

    /**
     * Verifica se l'allievo ha già superato un esame teorico
     * precedente alla data corrente.
     */
    public function haSuperatoEsameTeorico(int $idIscritto): bool
    {
        $conteggio = $this->em
            ->getRepository(EPrenotazioneEsami::class)
            ->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->join('p.esame', 'e')
            ->where('IDENTITY(p.allievo) = :idIscritto')
            ->andWhere('e.tipologia = :tipologia')
            ->andWhere('p.superato = :superato')
            ->andWhere('e.dataEs < CURRENT_TIMESTAMP()')
            ->setParameter('idIscritto', $idIscritto)
            ->setParameter('tipologia', 'TEORIA')
            ->setParameter('superato', true)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $conteggio > 0;
    }

    /**
     * Verifica se l'allievo è già prenotato per un esame
     * nella stessa data e ora.
     *
     * Serve ad impedire doppie prenotazioni.
     */
    public function haEsameInOrario(
        int $idIscritto,
        \DateTimeImmutable $dataOra
    ): bool {
        $conteggio = $this->em
            ->getRepository(EPrenotazioneEsami::class)
            ->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->join('p.esame', 'e')
            ->where('IDENTITY(p.allievo) = :idIscritto')
            ->andWhere('e.dataEs = :dataOra')
            ->andWhere('p.stato = :stato')
            ->setParameter('idIscritto', $idIscritto)
            ->setParameter('dataOra', $dataOra)
            ->setParameter('stato', 'PRENOTATO')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $conteggio > 0;
    }

    // ---------------- DELETE ----------------

    /**
     * Elimina una prenotazione.
     */
    public function delete(EPrenotazioneEsami $prenotazione): void
    {
        $this->em->remove($prenotazione);
        $this->em->flush();
    }
}
