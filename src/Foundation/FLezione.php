<?php
// src/Foundation/FLezione.php
// Classe Foundation / Service per ELezione

namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\ELezione;
use CamassoMedelago\DriveMeSafely\Entity\ELezioneTeoria;
use CamassoMedelago\DriveMeSafely\Entity\ELezionePratica;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\LockMode;
use DateTimeImmutable;
use CamassoMedelago\DriveMeSafely\Entity\EAula;

class FLezione
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    // ---------------- CREATE / UPDATE ----------------

    /**
     * Salva una nuova lezione o aggiorna una esistente.
     */
    public function save(ELezione $lezione): void
    {
        $this->em->persist($lezione);
        $this->em->flush();
    }

    /**
     * Aggiorna una lezione esistente.
     */
    public function update(ELezione $lezione): void
    {
        $this->em->flush();
    }

    // ---------------- READ ----------------

    /**
     * Recupera una lezione tramite il suo ID.
     */
    public function findById(int $idLezione): ?ELezione
    {
        return $this->em
            ->getRepository(ELezione::class)
            ->find($idLezione);
    }

    /**
     * Recupera e blocca uno slot durante la prenotazione.
     *
     * Il lock impedisce a due richieste contemporanee di confermare la stessa
     * guida pratica prima che una delle due abbia salvato la prenotazione.
     * Deve essere invocato all'interno di una transazione.
     */
    public function findByIdForUpdate(int $idLezione): ?ELezione
    {
        return $this->em
            ->getRepository(ELezione::class)
            ->find($idLezione, LockMode::PESSIMISTIC_WRITE);
    }

    /**
     * Recupera l'intero palinsesto delle lezioni,
     * ordinate dalla più recente alla più lontana nel tempo.
     */
    public function findAllPalinsesto(): array
    {
        return $this->em
            ->getRepository(ELezione::class)
            ->createQueryBuilder('l')
            ->orderBy('l.dataOra', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recupera le sole lezioni teoriche future relative
     * ad uno specifico argomento ministeriale.
     */
    public function findTeoriaByArgomento(string $argomentoCercato): array
    {
        if ($argomentoCercato === null || trim($argomentoCercato) === '') {
            return [];
        }

        return $this->em
            ->getRepository(ELezioneTeoria::class)
            ->createQueryBuilder('lt')
            ->where('lt.argomento = :argomento')
            ->andWhere('lt.dataOra > :oggi')
            ->setParameter('argomento', $argomentoCercato)
            ->setParameter('oggi', new DateTimeImmutable())
            ->orderBy('lt.dataOra', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recupera tutte le guide pratiche presenti nel palinsesto,
     * ordinate cronologicamente dalla più recente.
     */
    public function findAllGuidePratiche(): array
    {
        return $this->em
            ->getRepository(ELezionePratica::class)
            ->createQueryBuilder('lp')
            ->orderBy('lp.dataOra', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recupera i nomi degli istruttori già usati in precedenza, senza
     * duplicati. Serve a proporli come suggerimento in fase di
     * inserimento, per evitare che la segreteria digiti lo stesso nome
     * in modi diversi (maiuscole, spazi, refusi).
     *
     * @return string[]
     */
    public function findIstruttoriDistinti(): array
    {
        $righe = $this->em
            ->getRepository(ELezionePratica::class)
            ->createQueryBuilder('lp')
            ->select('DISTINCT lp.istruttore AS istruttore')
            ->where('lp.istruttore IS NOT NULL')
            ->orderBy('lp.istruttore', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_map(
            static fn (array $riga): string => (string) $riga['istruttore'],
            $righe
        );
    }

    /**
     * Recupera le targhe/vetture già usate in precedenza, senza
     * duplicati. Stesso scopo di findIstruttoriDistinti(): ridurre gli
     * errori di battitura che spezzerebbero il controllo di conflitto
     * su istruttoreVeicoloInUso().
     *
     * @return string[]
     */
    public function findVettureDistinte(): array
    {
        $righe = $this->em
            ->getRepository(ELezionePratica::class)
            ->createQueryBuilder('lp')
            ->select('DISTINCT lp.vettura AS vettura')
            ->where('lp.vettura IS NOT NULL')
            ->orderBy('lp.vettura', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_map(
            static fn (array $riga): string => (string) $riga['vettura'],
            $righe
        );
    }

    // ---------------- CONTROLLI DI DISPONIBILITÀ ----------------

    /**
     * Verifica se un istruttore o una vettura sono già occupati
     * in una determinata data e ora (o entro l'intervallo di 1 ora di durata).
     *
     * @param DateTimeImmutable $dataOra Inizio dello slot
     * @param string $istruttore Nome normalizzato dell'istruttore
     * @param string $vettura Targa normalizzata della vettura
     * @param int $durataMinuti Durata dello slot (default 60 minuti)
     */
    public function istruttoreVeicoloInUso(
        DateTimeImmutable $dataOra,
        string $istruttore,
        string $vettura,
        int $durataMinuti = 60
    ): bool {
        if ($istruttore === '' || $vettura === '') {
            return false;
        }

        $istruttoreNormalizzato = mb_strtoupper(preg_replace('/\s+/', ' ', trim($istruttore)));
        $vetturaNormalizzata = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $vettura));

        $inizioNuova = $dataOra;
        $fineNuova = $dataOra->modify("+{$durataMinuti} minutes");

        // Consideriamo le lezioni che iniziano nell'intervallo compreso tra 1 ora prima e 1 ora dopo
        $limiteInferiore = $dataOra->modify("-{$durataMinuti} minutes");
        $limiteSuperiore = $fineNuova;

        $lezioni = $this->em
            ->getRepository(ELezionePratica::class)
            ->createQueryBuilder('lp')
            ->where('lp.dataOra > :limiteInferiore AND lp.dataOra < :limiteSuperiore')
            ->setParameter('limiteInferiore', $limiteInferiore)
            ->setParameter('limiteSuperiore', $limiteSuperiore)
            ->getQuery()
            ->getResult();

        foreach ($lezioni as $lp) {
            /** @var ELezionePratica $lp */
            $istruttoreEsistente = mb_strtoupper(preg_replace('/\s+/', ' ', trim((string) $lp->getIstruttore())));
            $vetturaEsistente = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $lp->getVettura()));

            if ($istruttoreEsistente === $istruttoreNormalizzato || $vetturaEsistente === $vetturaNormalizzata) {
                $inizioEsistente = $lp->getDataOra();
                $fineEsistente = $inizioEsistente->modify("+{$durataMinuti} minutes");

                // Condizione di sovrapposizione tra due intervalli: inizio1 < fine2 AND inizio2 < fine1
                if ($inizioNuova < $fineEsistente && $inizioEsistente < $fineNuova) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verifica se un'aula è già occupata da una lezione teorica
     * nella stessa data e ora (o entro l'intervallo di 1 ora di durata).
     */
    public function aulaInUso(
        DateTimeImmutable $dataOra,
        string $aula,
        int $durataMinuti = 60
    ): bool {
        if ($aula === '') {
            return false;
        }

        $aulaEnum = EAula::tryFrom($aula);
        if ($aulaEnum === null) {
            return false;
        }

        $inizioNuova = $dataOra;
        $fineNuova = $dataOra->modify("+{$durataMinuti} minutes");

        $limiteInferiore = $dataOra->modify("-{$durataMinuti} minutes");
        $limiteSuperiore = $fineNuova;

        $lezioni = $this->em
            ->getRepository(ELezioneTeoria::class)
            ->createQueryBuilder('lt')
            ->where('lt.aula = :aula')
            ->andWhere('lt.dataOra > :limiteInferiore AND lt.dataOra < :limiteSuperiore')
            ->setParameter('aula', $aulaEnum)
            ->setParameter('limiteInferiore', $limiteInferiore)
            ->setParameter('limiteSuperiore', $limiteSuperiore)
            ->getQuery()
            ->getResult();

        foreach ($lezioni as $lt) {
            /** @var ELezioneTeoria $lt */
            $inizioEsistente = $lt->getDataOra();
            $fineEsistente = $inizioEsistente->modify("+{$durataMinuti} minutes");

            if ($inizioNuova < $fineEsistente && $inizioEsistente < $fineNuova) {
                return true;
            }
        }

        return false;
    }

    // ---------------- DELETE ----------------

    /**
     * Elimina una lezione.
     */
    public function delete(ELezione $lezione): void
    {
        $this->em->remove($lezione);
        $this->em->flush();
    }
}
