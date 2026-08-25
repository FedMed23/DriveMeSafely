<?php
// src/Foundation/FLezione.php
// Classe Foundation / Service per ELezione

namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\ELezione;
use CamassoMedelago\DriveMeSafely\Entity\ELezioneTeoria;
use CamassoMedelago\DriveMeSafely\Entity\ELezionePratica;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class FLezione
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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

    // ---------------- CONTROLLI DI DISPONIBILITÀ ----------------

    /**
     * Verifica se un istruttore o una vettura sono già occupati
     * in una determinata data e ora.
     *
     * Restituisce true se esiste almeno una guida pratica
     * che utilizza l'istruttore oppure la vettura indicata.
     */
    public function istruttoreVeicoloInUso(
        DateTimeImmutable $dataOra,
        string $istruttore,
        string $vettura
    ): bool {
        if (
            $dataOra === null ||
            $istruttore === null ||
            $vettura === null
        ) {
            return false;
        }

        $occupato = $this->em
            ->getRepository(ELezionePratica::class)
            ->createQueryBuilder('lp')
            ->select('COUNT(lp.idLezione)')
            ->where('lp.dataOra = :dataOra')
            ->andWhere(
                '(lp.istruttore = :istruttore OR lp.vettura = :vettura)'
            )
            ->setParameter('dataOra', $dataOra)
            ->setParameter('istruttore', $istruttore)
            ->setParameter('vettura', $vettura)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $occupato > 0;
    }

    /**
     * Verifica se un'aula è già occupata da una lezione teorica
     * nella stessa data e ora.
     */
    public function aulaInUso(
        DateTimeImmutable $dataOra,
        string $aula
    ): bool {
        if ($dataOra === null || $aula === null) {
            return false;
        }

        $occupata = $this->em
            ->getRepository(ELezioneTeoria::class)
            ->createQueryBuilder('lt')
            ->select('COUNT(lt.idLezione)')
            ->where('lt.dataOra = :dataOra')
            ->andWhere('lt.aula = :aula')
            ->setParameter('dataOra', $dataOra)
            ->setParameter('aula', $aula)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $occupata > 0;
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
?>
