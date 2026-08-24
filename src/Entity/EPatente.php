<?php

namespace CamassoMedelago\DriveMeSafely\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="patente")
 */
class EPatente implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer")
     */
    private ?int $idPa = null; 

    /**
     * @ORM\Column(type="string", length=2, nullable=false)  
     */
    private string $tipo;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $descrizione;

    /**
     * @ORM\ManyToMany(targetEntity="ESpesa")
     * @ORM\JoinTable(
     *     name="patente_has_spesa",
     *     joinColumns={@ORM\JoinColumn(name="id_patente", referencedColumnName="idPa")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="id_spesa", referencedColumnName="id")}
     * )
     * @ORM\OrderBy({"tipologia" = "ASC"})
     */
    private Collection $spese;

    /**
     * Costruttore
     */
    public function __construct(string $tipo, string $descrizione, Collection $spese = null)
    {
        $this->tipo = $tipo;
        $this->descrizione = $descrizione;
        $this->spese = $spese ?? new ArrayCollection();
    }

    // ---------------- GETTER / SETTER ----------------

    public function getId(): ?int 
    { 
        return $this->idPa; 
    }

    public function setId(int $id): void 
    { 
        $this->idPa = $id; 
    } 

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): void
    {
        $this->tipo = $tipo;
    }

    public function getDescrizione(): string
    {
        return $this->descrizione;
    }

    public function setDescrizione(string $descrizione): void
    {
        $this->descrizione = $descrizione;
    }

    public function getSpese(): Collection
    {
        return $this->spese;
    }

    public function setSpese(Collection $spese): void
    {
        $this->spese = $spese;
    }

    // ------------------ TOSTRING ---------------------------

    public function __toString(): string
    {
        return "Patente{idPa={$this->idPa}, tipo='{$this->tipo}', descrizione='{$this->descrizione}', numeroSpese=" . count($this->spese) . "}";
    }

    // ------------------ JSON SERIALIZE ---------------------

    public function jsonSerialize(): array
    {
        return [
            'idPa' => $this->idPa,
            'tipo' => $this->tipo,
            'descrizione' => $this->descrizione,
            'spese' => $this->spese->toArray()
        ];
    }
}
?>
