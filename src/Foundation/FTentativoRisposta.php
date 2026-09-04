<?php
// src/Foundation/FTentativoRisposta.php
// Classe Foundation / Service per ETentativoRisposta

namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\ETentativoRisposta;
use Doctrine\ORM\EntityManagerInterface;

class FTentativoRisposta
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    // ------------------- INSERIMENTO / SALVATAGGIO -------------------

    public function save(ETentativoRisposta $tentativo): void
    {
        $this->em->persist($tentativo);
        $this->em->flush();
    }

    // ------------------- RECUPERA PER ID -------------------

    public function getTentativoPerId(int $id): ?ETentativoRisposta
    {
        return $this->em
            ->getRepository(ETentativoRisposta::class)
            ->find($id);
    }

    // ------------------- AGGIORNAMENTO -------------------

    public function update(ETentativoRisposta $tentativo): void
    {
        $this->em->flush();
    }

    // ------------------- ELIMINAZIONE -------------------

    public function delete(ETentativoRisposta $tentativo): void
    {
        $this->em->remove($tentativo);
        $this->em->flush();
    }

    // ------------------- TENTATIVI PER SVOLGIMENTO -------------------

    /**
     * Recupera tutti i tentativi di risposta associati
     * a uno specifico svolgimento del quiz.
     *
     * @param int $svolgimentoId ID dello svolgimento
     * @return ETentativoRisposta[]
     */
    public function getTentativiPerSvolgimento(int $svolgimentoId): array
    {
        return $this->em
            ->getRepository(ETentativoRisposta::class)
            ->findBy([
                'svolgimento' => $svolgimentoId
            ]);
    }

    // ------------------- TENTATIVI PER DOMANDA -------------------

    /**
     * Recupera tutti i tentativi storici relativi
     * a una specifica domanda.
     *
     * @param int $domandaId ID della domanda
     * @return ETentativoRisposta[]
     */
    public function getTentativiPerDomanda(int $domandaId): array
    {
        return $this->em
            ->getRepository(ETentativoRisposta::class)
            ->findBy([
                'domanda' => $domandaId
            ]);
    }

    // ------------------- DOMANDE GIA' SVOLTE -------------------

    /**
     * Restituisce gli ID di tutte le domande che
     * l'iscritto ha già affrontato almeno una volta.
     *
     * Equivalente a:
     * SELECT DISTINCT tr.domanda.idDomanda
     * FROM TentativoRisposta tr
     * WHERE tr.svolgimentoQuiz.iscritto.id = :id
     *
     * @param int|null $idIscritto
     * @return int[]
     */
    public function findDomandeGiaSvolte(?int $idIscritto): array
    {
        if ($idIscritto === null) {
            return [];
        }

        $risultati = $this->em
            ->getRepository(ETentativoRisposta::class)
            ->createQueryBuilder('tr')
            ->select('DISTINCT d.idDomanda')
            ->join('tr.domanda', 'd')
            ->join('tr.svolgimentoQuiz', 'sq')
            ->join('sq.iscritto', 'i')
            ->where('i.id = :id')
            ->setParameter('id', $idIscritto)
            ->getQuery()
            ->getScalarResult();

        return array_map(
            fn($row) => (int) $row['idDomanda'],
            $risultati
        );
    }

    // ------------------- DOMANDE SBAGLIATE -------------------

    /**
     * Restituisce gli ID di tutte le domande
     * sbagliate dall'iscritto.
     *
     * @param int|null $idIscritto
     * @return int[]
     */
    public function findDomandeSbagliate(?int $idIscritto): array
    {
        if ($idIscritto === null) {
            return [];
        }

        $risultati = $this->em
            ->getRepository(ETentativoRisposta::class)
            ->createQueryBuilder('tr')
            ->select('DISTINCT d.idDomanda')
            ->join('tr.domanda', 'd')
            ->join('tr.svolgimentoQuiz', 'sq')
            ->join('sq.iscritto', 'i')
            ->where('i.id = :id')
            ->andWhere('tr.corretta = :corretta')
            ->setParameter('id', $idIscritto)
            ->setParameter('corretta', false)
            ->getQuery()
            ->getScalarResult();

        return array_map(
            fn($row) => (int) $row['idDomanda'],
            $risultati
        );
    }

    // ------------------- DOMANDE ORDINATE PER ERRORI -------------------

    /**
     * Restituisce gli ID delle domande ordinate
     * in modo decrescente in base al numero di errori.
     *
     * @param int|null $idIscritto
     * @return int[]
     */
    public function findDomandeOrdinatePerErrori(?int $idIscritto): array
    {
        if ($idIscritto === null) {
            return [];
        }

        $risultati = $this->em
            ->getRepository(ETentativoRisposta::class)
            ->createQueryBuilder('tr')
            ->select('d.idDomanda')
            ->join('tr.domanda', 'd')
            ->join('tr.svolgimentoQuiz', 'sq')
            ->join('sq.iscritto', 'i')
            ->where('i.id = :idIscritto')
            ->andWhere('tr.corretta = :corretta')
            ->groupBy('d.idDomanda')
            ->orderBy('COUNT(tr.id)', 'DESC')
            ->setParameter('idIscritto', $idIscritto)
            ->setParameter('corretta', false)
            ->getQuery()
            ->getScalarResult();

        return array_map(
            fn($row) => (int) $row['idDomanda'],
            $risultati
        );
    }

    // ------------------- STATISTICHE ERRORI -------------------

    /**
     * Genera un elenco di coppie:
     *
     * [
     *     [ID domanda, numero errori],
     *     ...
     * ]
     *
     * ordinato per numero di errori decrescente.
     *
     * @param int|null $idIscritto
     * @return array
     */
    public function findStatisticheErrori(?int $idIscritto): array
    {
        if ($idIscritto === null) {
            return [];
        }

        return $this->em
            ->getRepository(ETentativoRisposta::class)
            ->createQueryBuilder('tr')
            ->select('d.idDomanda AS idDomanda')
            ->addSelect('COUNT(tr.id) AS numeroErrori')
            ->join('tr.domanda', 'd')
            ->join('tr.svolgimentoQuiz', 'sq')
            ->join('sq.iscritto', 'i')
            ->where('i.id = :idIscritto')
            ->andWhere('tr.corretta = :corretta')
            ->groupBy('d.idDomanda')
            ->orderBy('COUNT(tr.id)', 'DESC')
            ->setParameter('idIscritto', $idIscritto)
            ->setParameter('corretta', false)
            ->getQuery()
            ->getArrayResult();
    }

    // ------------------- RISPOSTE CORRETTE -------------------

    /**
     * Restituisce il numero di risposte corrette
     * effettuate dall'iscritto.
     *
     * @param int $idIscritto
     * @return int
     */
    public function countRisposteCorretteByIscritto(int $idIscritto): int
    {
        return (int) $this->em
            ->getRepository(ETentativoRisposta::class)
            ->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->join('t.svolgimento', 'sq')
            ->join('sq.iscritto', 'i')
            ->where('i.id = :idIscritto')
            ->andWhere('t.corretta = :corretta')
            ->setParameter('idIscritto', $idIscritto)
            ->setParameter('corretta', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    // ------------------- NUMERO TENTATIVI -------------------

    /**
     * Restituisce il numero totale di tentativi
     * effettuati dall'iscritto.
     *
     * @param int $idIscritto
     * @return int
     */
    public function countTentativiByIscritto(int $idIscritto): int
    {
        return (int) $this->em
            ->getRepository(ETentativoRisposta::class)
            ->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->join('t.svolgimento', 'sq')
            ->join('sq.iscritto', 'i')
            ->where('i.id = :idIscritto')
            ->setParameter('idIscritto', $idIscritto)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

