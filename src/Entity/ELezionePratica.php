<?php

namespace CamassoMedelago\DriveMeSafely\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

/**
 * Classe figlia che rappresenta la prenotazione specifica
 * di una guida pratica.
 *
 * @ORM\Entity
 * @ORM\DiscriminatorValue("PRATICA")
 */
class ELezionePratica extends ELezione
{
    /**
     * Istruttore assegnato alla guida pratica.
     *
     * @ORM\Column(name="istruttore", type="string", length=100, nullable=true)
     */
    private ?string $istruttore = null;

    /**
     * Vettura utilizzata durante la guida.
     *
     * @ORM\Column(name="vettura", type="string", length=50, nullable=true)
     */
    private ?string $vettura = null;


    // ------------------------- COSTRUTTORE -------------------------

    public function __construct(
        ?DateTimeImmutable $dataOra = null,
        ?string $istruttore = null,
        ?string $vettura = null
    ) {
        parent::__construct($dataOra);

        $this->istruttore = $istruttore;
        $this->vettura = $vettura;
    }


    // ------------------------- METODI GET -------------------------

    public function getIstruttore(): ?string
    {
        return $this->istruttore;
    }

    public function getVettura(): ?string
    {
        return $this->vettura;
    }


    // ------------------------- METODI SET -------------------------

    public function setIstruttore(?string $istruttore): void
    {
        $this->istruttore = $istruttore;
    }

    public function setVettura(?string $vettura): void
    {
        $this->vettura = $vettura;
    }


    // ------------------------- TOSTRING -------------------------

    public function __toString(): string
    {
        $dataFormattata = isset($this->dataOra)
            ? $this->dataOra->format('d-m-Y H:i')
            : 'N/D';

        return "LezionePratica{" .
               "id=" . $this->getIdLezione() .
               ", dataOra=" . $dataFormattata .
               ", istruttore='" . $this->istruttore . '\'' .
               ", vettura='" . $this->vettura . '\'' .
               '}';
    }
}
