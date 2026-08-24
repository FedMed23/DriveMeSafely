<?php 

namespace CamassoMedelago\DriveMeSafely\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="spesa")
 */
class ESpesa implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer", name="id_spesa")
     */
    private ?int $idSpesa = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $tipologia;

    /**
     * @ORM\Column(type="float", nullable=false)
     */
    private float $importo;

    /**
     * Valori consigliati: "PATENTE" o "PROPRIETARIO"
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private string $ambito;


    // ---------------- COSTRUTTORE ----------------

    /**
     * Crea una nuova istanza della classe ESpesa
     */
    public function __construct(string $tipologia, float $importo, ?string $ambito = "PROPRIETARIO")
    {
        $this->tipologia = $tipologia;
        $this->importo = $importo;
        $this->ambito = $ambito;
    }

    // ---------------- METODI GET ----------------

    public function getIdSpesa(): ?int
    {
        return $this->idSpesa;
    }

    public function getTipologia(): string
    {
        return $this->tipologia;
    }

    public function getImporto(): float
    {
        return $this->importo;
    }

    public function getAmbito(): string
    {
        return $this->ambito;
    }

    // ---------------- METODI SET ----------------

    public function setIdSpesa(int $idSpesa): void
    {
        $this->idSpesa = $idSpesa;
    }

    public function setTipologia(string $tipologia): void
    {
        $this->tipologia = $tipologia;
    }

    public function setImporto(float $importo): void
    {
        $this->importo = $importo;
    }

    public function setAmbito(?string $ambito): void
    {
        $this->ambito = $ambito;
    }

    // ------------------ TOSTRING ---------------------------

    public function __toString(): string
    {
        return "Spesa{idSpesa={$this->idSpesa}, tipologia='{$this->tipologia}', importo=€{$this->importo}, ambito='{$this->ambito}'}";
    }

    // ------------------ JSON SERIALIZE ---------------------

    public function jsonSerialize(): array
    {
        return [
            'idSpesa' => $this->idSpesa,
            'tipologia' => $this->tipologia,
            'importo' => $this->importo,
            'ambito' => $this->ambito
        ];
    }
}
?>
