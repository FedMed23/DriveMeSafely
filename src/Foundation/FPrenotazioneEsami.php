<?php

namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\EPrenotazioneEsami;
use CamassoMedelago\DriveMeSafely\Entity\StatoPrenotazioneEsame;
use CamassoMedelago\DriveMeSafely\Entity\TipologiaEsame;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class FPrenotazioneEsami
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
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
     * Recupera una prenotazione per allievo e sessione d'esame.
     */
    public function findByIscrittoAndEsame(int $idIscritto, int $idEsame): ?EPrenotazioneEsami
    {
        return $this->em
            ->getRepository(EPrenotazioneEsami::class)
            ->createQueryBuilder('p')
            ->where('p.allievo = :idIscritto')
            ->andWhere('p.esame = :idEsame')
            ->setParameter('idIscritto', $idIscritto)
            ->setParameter('idEsame', $idEsame)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Verifica se un allievo ha già una prenotazione attiva per una determinata sessione d'esame.
     */
    public function isIscrittoGiaPrenotatoAdEsame(int $idIscritto, int $idEsame): bool
    {
        $conteggio = $this->em
            ->getRepository(EPrenotazioneEsami::class)
            ->createQueryBuilder('p')
            ->select('COUNT(p.idPrenotazioneEsame)')
            ->where('p.allievo = :idIscritto')
            ->andWhere('p.esame = :idEsame')
            ->andWhere('p.stato = :stato')
            ->setParameter('idIscritto', $idIscritto)
            ->setParameter('idEsame', $idEsame)
            ->setParameter('stato', StatoPrenotazioneEsame::PRENOTATO)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $conteggio > 0;
    }

    /**
     * Conta quante volte un determinato allievo ha già sostenuto
     * o è stato prenotato per un esame della stessa tipologia.
     *
     * @param int $idIscritto
     * @param string|TipologiaEsame $tipologia
     * @return int
     */
    public function contaTentativiPrecedenti(
        int $idIscritto,
        string|TipologiaEsame $tipologia
    ): int {
        $tipo = $tipologia instanceof TipologiaEsame ? $tipologia : TipologiaEsame::tryFrom($tipologia);

        $conteggio = $this->em
            ->getRepository(EPrenotazioneEsami::class)
            ->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->join('p.esame', 'e')
            ->where('IDENTITY(p.allievo) = :idIscritto')
            ->andWhere('e.tipologia = :tipologia')
            ->setParameter('idIscritto', $idIscritto)
            ->setParameter('tipologia', $tipo ?? $tipologia)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $conteggio;
    }

    /**
     * Verifica se l'allievo ha già superato un esame teorico.
     * L'esito è ricavato dalle effettuazioni collegate alla prenotazione (relazione 1:N).
     */
    public function haSuperatoEsameTeorico(int $idIscritto): bool
    {
        $conteggio = $this->em
            ->getRepository(\CamassoMedelago\DriveMeSafely\Entity\EEffettuazioneEsami::class)
            ->createQueryBuilder('eff')
            ->select('COUNT(eff)')
            ->join('eff.prenotazioneEsame', 'p')
            ->join('p.esame', 'e')
            ->where('IDENTITY(p.allievo) = :idIscritto')
            ->andWhere('e.tipologia = :tipologia')
            ->andWhere('eff.superato = :superato')
            ->setParameter('idIscritto', $idIscritto)
            ->setParameter('tipologia', TipologiaEsame::TEORIA)
            ->setParameter('superato', true)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $conteggio > 0;
    }

    /**
     * Verifica se l'allievo ha già superato un esame pratico.
     * L'esito è ricavato dalle effettuazioni collegate alla prenotazione (relazione 1:N).
     */
    public function haSuperatoEsamePratico(int $idIscritto): bool
    {
        $conteggio = $this->em
            ->getRepository(\CamassoMedelago\DriveMeSafely\Entity\EEffettuazioneEsami::class)
            ->createQueryBuilder('eff')
            ->select('COUNT(eff)')
            ->join('eff.prenotazioneEsame', 'p')
            ->join('p.esame', 'e')
            ->where('IDENTITY(p.allievo) = :idIscritto')
            ->andWhere('e.tipologia = :tipologia')
            ->andWhere('eff.superato = :superato')
            ->setParameter('idIscritto', $idIscritto)
            ->setParameter('tipologia', TipologiaEsame::PRATICA)
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
            ->setParameter('stato', StatoPrenotazioneEsame::PRENOTATO)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $conteggio > 0;
    }

    /**
     * Verifica se un allievo ha già un esame futuro attivo (PRENOTATO) per una determinata tipologia.
     */
    public function haPrenotazioneFuturaAttivaPerTipologia(
        int $idIscritto,
        string|TipologiaEsame $tipologia
    ): bool {
        $tipo = $tipologia instanceof TipologiaEsame ? $tipologia : TipologiaEsame::tryFrom($tipologia);

        $conteggio = $this->em
            ->getRepository(EPrenotazioneEsami::class)
            ->createQueryBuilder('p')
            ->select('COUNT(p.idPrenotazioneEsame)')
            ->join('p.esame', 'e')
            ->where('p.allievo = :idIscritto')
            ->andWhere('e.tipologia = :tipologia')
            ->andWhere('p.stato = :stato')
            ->andWhere('e.dataEs > CURRENT_TIMESTAMP()')
            ->setParameter('idIscritto', $idIscritto)
            ->setParameter('tipologia', $tipo ?? $tipologia)
            ->setParameter('stato', StatoPrenotazioneEsame::PRENOTATO)
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
