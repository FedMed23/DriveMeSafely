<?php

namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\ESvolgimentoQuiz;
use Doctrine\ORM\EntityManagerInterface;

class FSvolgimentoQuiz
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    // ------------------- SALVATAGGIO -------------------

    public function save(ESvolgimentoQuiz $svolgimento): void
    {
        $this->em->persist($svolgimento);
        $this->em->flush();
    }

    // ------------------- RICERCA PER ID -------------------

    public function getSvolgimentoPerId(int $id): ?ESvolgimentoQuiz
    {
        return $this->em
            ->getRepository(ESvolgimentoQuiz::class)
            ->find($id);
    }

    // ------------------- AGGIORNAMENTO -------------------

    public function update(ESvolgimentoQuiz $svolgimento): void
    {
        $this->em->flush();
    }

    // ------------------- ELIMINAZIONE -------------------

    public function delete(ESvolgimentoQuiz $svolgimento): void
    {
        $this->em->remove($svolgimento);
        $this->em->flush();
    }

    // ------------------- RICERCA DI TUTTI -------------------

    /**
     * Restituisce tutti gli svolgimenti,
     * dal più recente al più vecchio.
     */
    public function findAll(): array
    {
        return $this->em->createQuery(
            'SELECT s
             FROM ' . ESvolgimentoQuiz::class . ' s
             ORDER BY s.dataSvolgimento DESC'
        )->getResult();
    }

    // ------------------- RICERCA PER ISCRITTO -------------------

    /**
     * Restituisce tutti gli svolgimenti
     * effettuati da uno specifico iscritto.
     */
    public function getSvolgimentiPerIscritto(int $idIscritto): array
    {
        return $this->em
            ->getRepository(ESvolgimentoQuiz::class)
            ->findBy(
                ['iscritto' => $idIscritto],
                ['dataSvolgimento' => 'DESC']
            );
    }

    // ------------------- RICERCA PER QUIZ -------------------

    /**
     * Restituisce tutti gli svolgimenti
     * relativi ad uno specifico quiz.
     */
    public function getSvolgimentiPerQuiz(int $quizId): array
    {
        return $this->em
            ->getRepository(ESvolgimentoQuiz::class)
            ->findBy(
                ['quiz' => $quizId],
                ['dataSvolgimento' => 'DESC']
            );
    }

    // ------------------- QUIZ SUPERATI -------------------

    /**
     * Restituisce il numero di quiz superati dall'iscritto.
     */
    public function contaQuizSuperatiByIscritto(?int $idIscritto): int
    {
        if ($idIscritto === null) {
            return 0;
        }

        $query = $this->em->createQuery(
            'SELECT COUNT(s)
             FROM ' . ESvolgimentoQuiz::class . ' s
             WHERE IDENTITY(s.iscritto) = :idIscritto
             AND s.superato = true'
        );

        $query->setParameter('idIscritto', $idIscritto);

        return (int) $query->getSingleScalarResult();
    }

    // ------------------- QUIZ SVOLTI -------------------

    /**
     * Restituisce il numero totale di quiz svolti dall'iscritto.
     */
    public function contaQuizSvoltiByIscritto(?int $idIscritto): int
    {
        if ($idIscritto === null) {
            return 0;
        }

        $query = $this->em->createQuery(
            'SELECT COUNT(s)
             FROM ' . ESvolgimentoQuiz::class . ' s
             WHERE IDENTITY(s.iscritto) = :idIscritto'
        );

        $query->setParameter('idIscritto', $idIscritto);

        return (int) $query->getSingleScalarResult();
    }
}
