<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * La classe EPatente contiene le proprietà e gli attributi riguardanti una patente di guida.
 * L'attributo che la descrive è:
 * - idPa: id della Patente
 * - tipo: categoria della patente (es. B, A, C, ecc.)
 * 
 * @access public
 * @author Camasso-Medelago
 * @package Entity
 * @ORM\Entity
 * @ORM\Table(name="patente")
 */

class EPatente implements JsonSerializable
{
    /**
     * idPa identificativo della patente (chiave primaria)
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="int", length=100)
     */
     private ?int $idPa= null; 

    /**
     * Tipo della patente (es. B, A, C)
     * @var string
     * @ORM\Column(type="string", length=2)  
     */
    private string $tipo;


   // ---------------- COSTRUTTORE ----------------

    /**
     * Crea una nuova istanza della classe EPatente
     * @param string $tipo categoria della patente
     */
    public function __construct(string $tipo)
    {
        $this->tipo = $tipo;
    }
 //----------------------METODI GET/SET (ID)-----------------------------
    /**
     * Restituisce l'id della patente
     * @return int
     */
    public function getId(): ?int { return $this->idPa; }

    /**
     * Imposta l'id della patente
     * @param int
     */
    public function setId(int $id): void { $this->idPa= $id; } 

// ---------------- METODI GET ----------------

    /**
     * Restituisce il tipo della patente
     * @return string
     */
    public function getTipo(): string
    {
        return $this->tipo;
    }

// ---------------- METODI SET ----------------

    /**
     * Imposta il tipo della patente
     * @param string $tipo
     */
    public function setTipo(string $tipo): void
    {
        $this->tipo = $tipo;
    }

// ------------------ TOSTRING ---------------------------

    /**
     * Stampa i dettagli della patente
     * @return string
     */
    public function __toString(): string
    {
        // Restituisce una stringa che include i dettagli chiave
        return "idPatente: {$this->idPa}\n Tipo di Patente: {$this->tipo}\n";
    }

  // --- Implementazione per la serializzazione JSON ---

    /**
     * Serializza l'oggetto in formato JSON
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'idPa' => $this->idPa,
            'tipo' => $this->tipo
        ];
    }
}

?> 

