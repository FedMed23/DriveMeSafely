<?php
namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\EPrenotazioneGuida;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class FPrenotazioneGuida
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // ---------------- CREATE / UPDATE ----------------
    /**
     * Salva una nuova prenotazione o aggiorna una esistente
     */
    public function save(EPrenotazioneGuida $prenotazione): void
    {
        $this->em->persist($prenotazione);
        $this->em->flush();
    }

    // ---------------- READ ----------------
    /**
     * Recupera una prenotazione per ID
     */
    public function findById(int $id): ?EPrenotazioneGuida
    {
        return $this->em->getRepository(EPrenotazioneGuida::class)->find($id);
    }

    /**
     * Recupera tutte le prenotazioni
     */
    public function findAll(): array
    {
        return $this->em->getRepository(EPrenotazioneGuida::class)->findAll();
    }

    /**
     * Recupera tutte le prenotazioni di un iscritto
     */
    public function findByIscritto(int $idIscritto): array
    {
        return $this->em->getRepository(EPrenotazioneGuida::class)->findBy([
            'idIscritto' => $idIscritto
        ]);
    }

    /**
     * Recupera tutte le prenotazioni per stato
     */
    public function findByStato(string $stato): array
    {
        return $this->em->getRepository(EPrenotazioneGuida::class)->findBy([
            'stato' => $stato
        ]);
    }

    // ---------------- DELETE ----------------
    /**
     * Elimina una prenotazione
     */
    public function delete(EPrenotazioneGuida $prenotazione): void
    {
        $this->em->remove($prenotazione);
        $this->em->flush();
    }
}
?>
