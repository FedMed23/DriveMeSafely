<?php
// src/Foundation/FPrenotazioneLezione.php
// Classe Foundation / Service per EPrenotazioneLezione

namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\EPrenotazioneLezione;
use CamassoMedelago\DriveMeSafely\Entity\ELezione;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class FPrenotazioneLezione
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
    public function save(EPrenotazioneLezione $prenotazione): void
    {
        $this->em->persist($prenotazione);
        $this->em->flush();
    }

    /**
     * Aggiorna una prenotazione esistente.
     */
    public function update(EPrenotazioneLezione $prenotazione): void
    {
        $this->em->flush();
    }

    // ---------------- READ ----------------

    /**
     * Recupera una prenotazione tramite il suo ID.
     */
    public function findById(int $id): ?EPrenotazioneLezione
    {
        return $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->find($id);
    }

    /**
     * Recupera tutte le prenotazioni di un determinato iscritto,
     * ordinate cronologicamente dalla lezione più recente.
     */
    public function findByIscrittoId(int $idIscritto): array
    {
        return $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->createQueryBuilder('p')
            ->join('p.lezione', 'l')
            ->where('p.iscritto = :idIscritto')
            ->setParameter('idIscritto', $idIscritto)
            ->orderBy('l.dataOra', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recupera tutte le lezioni future disponibili per un iscritto,
     * escludendo quelle per cui esiste già una prenotazione attiva.
     */
    public function findLezioniDisponibili(int $idIscritto): array
    {
        $qb = $this->em
            ->getRepository(ELezione::class)
            ->createQueryBuilder('l');

        $subQuery = $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->createQueryBuilder('p')
            ->select('IDENTITY(p.lezione)')
            ->where('p.iscritto = :idIscritto')
            ->andWhere('p.stato != :statoAnnullata')
            ->getDQL();

        return $qb
            ->where('l.dataOra > :oggi')
            ->andWhere($qb->expr()->notIn('l.idLezione', $subQuery))
            ->setParameter('oggi', new DateTimeImmutable())
            ->setParameter('idIscritto', $idIscritto)
            ->setParameter('statoAnnullata', 'ANNULLATA')
            ->orderBy('l.dataOra', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recupera tutte le prenotazioni collegate a una specifica lezione,
     * escludendo quelle annullate e ordinando gli iscritti per cognome.
     */
    public function findByLezioneId(int $idLezione): array
    {
        return $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->createQueryBuilder('p')
            ->join('p.iscritto', 'i')
            ->where('p.lezione = :idLezione')
            ->andWhere('p.stato != :statoAnnullata')
            ->setParameter('idLezione', $idLezione)
            ->setParameter('statoAnnullata', 'ANNULLATA')
            ->orderBy('i.cognome', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recupera tutte le prenotazioni.
     */
    public function findAll(): array
    {
        return $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->findAll();
    }

    // ---------------- CONTROLLI ----------------

    /**
     * Verifica se un iscritto ha già una lezione prenotata
     * nello stesso giorno e orario.
     */
    public function haLezioneInOrario(
        int $idIscritto,
        DateTimeImmutable $dataOra
    ): bool {
        $conteggio = $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->createQueryBuilder('p')
            ->select('COUNT(p.idPrenotazione)')
            ->join('p.lezione', 'l')
            ->where('p.iscritto = :idIscritto')
            ->andWhere('l.dataOra = :dataOra')
            ->andWhere('p.stato != :statoAnnullata')
            ->setParameter('idIscritto', $idIscritto)
            ->setParameter('dataOra', $dataOra)
            ->setParameter('statoAnnullata', 'ANNULLATA')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $conteggio > 0;
    }

    /**
     * Verifica se una lezione teorica ha raggiunto
     * la capienza massima dell'aula.
     */
    public function isAulaPiena(
        int $idLezione,
        int $capienzaMassima
    ): bool {
        $iscrittiPrenotati = $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->createQueryBuilder('p')
            ->select('COUNT(p.idPrenotazione)')
            ->where('p.lezione = :idLezione')
            ->andWhere('p.stato != :statoAnnullata')
            ->setParameter('idLezione', $idLezione)
            ->setParameter('statoAnnullata', 'ANNULLATA')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $iscrittiPrenotati >= $capienzaMassima;
    }

    // ---------------- DELETE ----------------

    /**
     * Elimina una prenotazione.
     */
    public function delete(EPrenotazioneLezione $prenotazione): void
    {
        $this->em->remove($prenotazione);
        $this->em->flush();
    }
}
?>
