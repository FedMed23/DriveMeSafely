<?php
// src/Foundation/FSvolgimentoQuiz.php
// Classe Foundation / Service per ESvolgimentoQuiz

namespace DriveMeSafely\src\Foundation;
use DriveMeSafely\src\Entity\ESvolgimentoQuiz;
use Doctrine\ORM\EntityManagerInterface;

class FSvolgimentoQuiz
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // Inserimento / Salvataggio
    public function save(ESvolgimentoQuiz $svolgimento): void
    {
        $this->em->persist($svolgimento);
        $this->em->flush();
    }

    // Recupera uno svolgimento per ID
    public function getSvolgimentoPerId(int $id): ?ESvolgimentoQuiz
    {
        return $this->em->getRepository(ESvolgimentoQuiz::class)->find($id);
    }

    // Aggiorna lo svolgimento
    public function update(ESvolgimentoQuiz $svolgimento): void
    {
        $this->em->flush();
    }

    // Elimina lo svolgimento
    public function delete(ESvolgimentoQuiz $svolgimento): void
    {
        $this->em->remove($svolgimento);
        $this->em->flush();
    }

    // Recupera tutti gli svolgimenti di un utente (id iscritto)
    public function getSvolgimentiPerIscritto(int $idIscritto): array
    {
        return $this->em->getRepository(ESvolgimentoQuiz::class)->findBy([
            'idIscritto' => $idIscritto
        ]);
    }

    // Recupera tutti gli svolgimenti di un quiz specifico
    public function getSvolgimentiPerQuiz(int $quizId): array
    {
        return $this->em->getRepository(ESvolgimentoQuiz::class)->findBy([
            'quiz' => $quizId
        ]);
    }
}
?>
