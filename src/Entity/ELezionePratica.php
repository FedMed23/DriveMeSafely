<?php

namespace CamassoMedelago\DriveMeSafely\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

/**
 * Classe figlia che rappresenta la prenotazione specifica
 * di una guida pratica.
 *
 * Nota: il valore del discriminatore ("PRATICA") è già definito dalla mappa
 * dei discriminatori sulla classe madre ELezione (attributo DiscriminatorMap);
 * l'equivalente annotazione DiscriminatorValue in stile docblock non esiste
 * più in questa versione di Doctrine (sostituita dall'attributo PHP nativo),
 * ma non è comunque necessaria qui perché la mappa è già completa.
 *
 * @ORM\Entity
 */
#[ORM\Entity]
#[ORM\DiscriminatorValue('PRATICA')]
class ELezionePratica extends ELezione
{
    /**
     * Istruttore assegnato alla guida pratica.
     *
     * @var string|null
     * @ORM\Column(name="istruttore", type="string", length=100, nullable=true)
     */
    #[ORM\Column(name: 'istruttore', type: 'string', length: 100, nullable: true)]
    private ?string $istruttore = null;

    /**
     * Vettura utilizzata durante la guida.
     *
     * @var string|null
     * @ORM\Column(name="vettura", type="string", length=50, nullable=true)
     */
    #[ORM\Column(name: 'vettura', type: 'string', length: 50, nullable: true)]
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
