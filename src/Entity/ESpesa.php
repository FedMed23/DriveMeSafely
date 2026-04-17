<?php 

namespace CamassoMedelago\DriveMeSafely\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * La classe ESpesa contiene le proprietà e gli attributi riguardanti le spese che dve sostenere una scuola guida.
 * Gli attributi che la descrivono sono:
 * - idSpesa: identificativo univoco della spesa
 * - tipologia: tipo di spesa (es. tassa, bollo, assicurazione, stipendio, iscrizione ecc.)
 * - importo: importo della spesa
 * @access public
 * @author Camasso-Medelago
 * @package Entity
 * @ORM\Entity
 * @ORM\Table(name="spesa")
 */

class ESpesa implements \JsonSerializable
{
    /**
     * Identificativo univoco della spesa
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private ?int $idSpesa=null;

    /**
     * Tipologia della spesa (es. tassa, bollo, stipendio, iscrizione ecc.)
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    private string $tipologia;

    /**
     * Importo della spesa
     * @var float
     * @ORM\Column(type="float")
     */
    private float $importo;


    // ---------------- COSTRUTTORE ----------------

    /**
     * Crea una nuova istanza della classe ESpesa
     *
     * @param string $tipologia tipo di spesa
     * @param float $importo importo della spesa
     */
    public function __construct(string $tipologia, float $importo)
    {
        $this->tipologia = $tipologia;
        $this->importo = $importo;
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

    // ------------------ TOSTRING ---------------------------

    /**
     * Stampa i dettagli della spesa
     * @return string
     */
    public function __toString(): string
    {
        return "ID Spesa: {$this->idSpesa}\nTipologia: {$this->tipologia}\nImporto: €{$this->importo}\n";
    }

    // --- Implementazione per la serializzazione JSON ---

    /**
     * Serializza l'oggetto in formato JSON
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'idSpesa' => $this->idSpesa,
            'tipologia' => $this->tipologia,
            'importo' => $this->importo,
        ];
    }
}

?>
