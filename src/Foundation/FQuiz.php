<?php

namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\EQuiz;
use CamassoMedelago\DriveMeSafely\Entity\EDomanda;
use Doctrine\ORM\EntityManagerInterface;

class FQuiz
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    // ---------------- CREATE / UPDATE ----------------

    /**
     * Salva un quiz nuovo o aggiornato
     */
    public function save(EQuiz $quiz): void
    {
        $this->em->persist($quiz);
        $this->em->flush();
    }

    // ---------------- READ ----------------

    /**
     * Recupera un quiz per ID
     */
    public function findById(int $id): ?EQuiz
    {
        return $this->em
            ->getRepository(EQuiz::class)
            ->find($id);
    }

    /**
     * Recupera tutti i quiz ordinati alfabeticamente per nome.
     */
    public function findAll(): array
    {
        return $this->em->createQuery(
            'SELECT q
             FROM ' . EQuiz::class . ' q
             ORDER BY q.nome ASC'
        )->getResult();
    }

    /**
     * Recupera un quiz per nome.
     *
     * Il confronto:
     * - ignora maiuscole/minuscole
     * - ignora gli spazi iniziali e finali
     *
     * Restituisce null se non viene trovato alcun quiz.
     */
    public function findByNome(string $nomeQuiz): ?EQuiz
    {
        $query = $this->em->createQuery(
            'SELECT q
             FROM ' . EQuiz::class . ' q
             WHERE LOWER(TRIM(q.nome)) = LOWER(TRIM(:nome))
             ORDER BY q.nome ASC'
        );

        $query->setParameter('nome', $nomeQuiz);

        $quiz = $query->getResult();

        return empty($quiz) ? null : $quiz[0];
    }

    /**
     * Conta il numero totale di quiz presenti.
     */
    public function contaQuiz(): int
    {
        $query = $this->em->createQuery(
            'SELECT COUNT(q)
             FROM ' . EQuiz::class . ' q'
        );

        return (int) $query->getSingleScalarResult();
    }

    // ---------------- RELAZIONI DOMANDE ----------------

    /**
     * Aggiunge una domanda a un quiz.
     */
    public function addDomanda(EQuiz $quiz, EDomanda $domanda): void
    {
        $quiz->addDomanda($domanda);

        $this->em->persist($quiz);
        $this->em->flush();
    }

    /**
     * Rimuove una domanda da un quiz.
     */
    public function removeDomanda(EQuiz $quiz, EDomanda $domanda): void
    {
        $quiz->removeDomanda($domanda);

        $this->em->persist($quiz);
        $this->em->flush();
    }

    // ---------------- DELETE ----------------

    /**
     * Elimina un quiz.
     */
    public function delete(EQuiz $quiz): void
    {
        $this->em->remove($quiz);
        $this->em->flush();
    }
}
