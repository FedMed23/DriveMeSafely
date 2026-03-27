<?php
namespace DriveMeSafely\src\Foundation;

use DriveMeSafely\src\Entity\EQuiz;
use DriveMeSafely\src\Entity\EDomanda;
use Doctrine\ORM\EntityManagerInterface;

class FQuiz
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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
        return $this->em->getRepository(EQuiz::class)->find($id);
    }

    /**
     * Recupera tutti i quiz
     */
    public function findAll(): array
    {
        return $this->em->getRepository(EQuiz::class)->findAll();
    }

    /**
     * Recupera quiz per nome
     */
    public function findByNome(string $nomeQuiz): array
    {
        return $this->em->getRepository(EQuiz::class)->findBy([
            'nomeQuiz' => $nomeQuiz
        ]);
    }

    // ---------------- RELAZIONI DOMANDE ----------------
    /**
     * Aggiunge una domanda a un quiz
     */
    public function addDomanda(EQuiz $quiz, EDomanda $domanda): void
    {
        $quiz->addDomanda($domanda);
        $this->em->persist($quiz);
        $this->em->flush();
    }

    /**
     * Rimuove una domanda da un quiz
     */
    public function removeDomanda(EQuiz $quiz, EDomanda $domanda): void
    {
        $quiz->removeDomanda($domanda);
        $this->em->persist($quiz);
        $this->em->flush();
    }

    // ---------------- DELETE ----------------
    /**
     * Elimina un quiz
     */
    public function delete(EQuiz $quiz): void
    {
        $this->em->remove($quiz);
        $this->em->flush();
    }
}
?>
