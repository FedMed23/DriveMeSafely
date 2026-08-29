<?php

namespace CamassoMedelago\DriveMeSafely\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

/**
 * Classe madre astratta per la gestione polimorfica di tutte le attività
 * didattiche prenotabili dall'allievo (guide pratiche o lezioni di teoria).
 *
 * @ORM\Entity
 * @ORM\Table(name="lezione")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="tipo_lezione", type="string", length=20)
 */
abstract class ELezione
{
    /**
     * Identificativo univoco della lezione.
     *
     * @var int|null
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id_lezione", type="integer")
     */
    protected ?int $idLezione = null;

    /**
     * Data e ora della lezione.
     *
     * @var DateTimeImmutable
     * @ORM\Column(name="data_ora", type="datetime_immutable")
     */
    protected DateTimeImmutable $dataOra;


    // ------------------------- COSTRUTTORI -------------------------

    /**
     * Costruttore vuoto richiesto da Doctrine.
     */
    public function __construct(?DateTimeImmutable $dataOra = null)
    {
        if ($dataOra !== null) {
            $this->dataOra = $dataOra;
        }
    }


    // ------------------------- METODI GET -------------------------

    /**
     * Restituisce l'identificativo della lezione.
     */
    public function getIdLezione(): ?int
    {
        return $this->idLezione;
    }

    /**
     * Restituisce la data e ora della lezione.
     */
    public function getDataOra(): DateTimeImmutable
    {
        return $this->dataOra;
    }

    /**
     * Restituisce il tipo concreto della lezione.
     *
     * Esempio:
     * - ELezioneTeoria
     * - ELezionePratica
     */
    public function getTipo(): string
    {
        return static::class;
    }

    /**
     * Restituisce la data e ora formattata.
     *
     * Formato: dd/mm/yyyy HH:mm
     */
    public function getDataOraFormattata(): string
    {
        if (!isset($this->dataOra)) {
            return '';
        }

        return $this->dataOra->format('d/m/Y H:i');
    }


    // ------------------------- METODI SET -------------------------

    public function setIdLezione(int $idLezione): void
    {
        $this->idLezione = $idLezione;
    }

    public function setDataOra(DateTimeImmutable $dataOra): void
    {
        $this->dataOra = $dataOra;
    }


    // ------------------------- EQUALS & HASHCODE -------------------------

    /**
     * Confronta due lezioni sulla base dell'identificativo.
     */
    public function equals(?ELezione $altraLezione): bool
    {
        if ($this === $altraLezione) {
            return true;
        }

        if ($altraLezione === null) {
            return false;
        }

        return $this->idLezione !== null
            && $this->idLezione === $altraLezione->getIdLezione();
    }


    // ------------------------- TOSTRING -------------------------

    public function __toString(): string
    {
        $id = $this->idLezione ?? 'N/D';

        $data = isset($this->dataOra)
            ? $this->dataOra->format('d/m/Y H:i')
            : 'N/D';

        return "ID Lezione: {$id}\n" .
               "Tipo: {$this->getTipo()}\n" .
               "Data e Ora: {$data}\n";
    }
}
