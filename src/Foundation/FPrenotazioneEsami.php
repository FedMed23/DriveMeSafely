<?php
namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\EPrenotazioneEsami;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class FPrenotazioneEsami
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
    public function save(EPrenotazioneEsami $prenotazione): void
    {
        $this->em->persist($prenotazione);
        $this->em->flush();
    }

    // ---------------- READ ----------------
    /**
     * Recupera una prenotazione per ID
     */
    public function findById(int $id): ?EPrenotazioneEsami
    {
        return $this->em->getRepository(EPrenotazioneEsami::class)->find($id);
    }

    /**
     * Recupera tutte le prenotazioni
     */
    public function findAll(): array
    {
        return $this->em->getRepository(EPrenotazioneEsami::class)->findAll();
    }

    /**
     * Recupera tutte le prenotazioni di un dipendente
     */
    public function findByDipendente(int $idDipendente): array
    {
        return $this->em->getRepository(EPrenotazioneEsami::class)->findBy([
            'idDipendente' => $idDipendente
        ]);
    }

    /**
     * Recupera tutte le prenotazioni per stato
     */
    public function findByStato(string $stato): array
    {
        return $this->em->getRepository(EPrenotazioneEsami::class)->findBy([
            'stato' => $stato
        ]);
    }

    // ---------------- DELETE ----------------
    /**
     * Elimina una prenotazione
     */
    public function delete(EPrenotazioneEsami $prenotazione): void
    {
        $this->em->remove($prenotazione);
        $this->em->flush();
    }
}
?>
