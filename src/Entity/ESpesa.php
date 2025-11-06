<?php 
/**
 * La classe ESpesa contiene le proprietà e gli attributi riguardanti una spesa legata alla patente di guida.
 * Gli attributi che la descrivono sono:
 * - idSpesa: identificativo univoco della spesa
 * - tipologia: tipo di spesa (es. tassa, bollo, assicurazione, ecc.)
 * - importo: importo della spesa
 * @access public
 * @author Camasso-Medelago
 * @package Entity
 */

class ESpesa implements JsonSerializable
{
    /**
     * Identificativo univoco della spesa
     * @var int
     */
    private int $idSpesa;

    /**
     * Tipologia della spesa (es. tassa, bollo, ecc.)
     * @var string
     */
    private string $tipologia;

    /**
     * Importo della spesa
     * @var float
     */
    private float $importo;


    // ---------------- COSTRUTTORE ----------------

    /**
     * Crea una nuova istanza della classe ESpesa
     * 
     * @param int $idSpesa identificativo univoco della spesa
     * @param string $tipologia tipo di spesa
     * @param float $importo importo della spesa
     */
    public function __construct(int $idSpesa, string $tipologia, float $importo)
    {
        $this->idSpesa = $idSpesa;
        $this->tipologia = $tipologia;
        $this->importo = $importo;
    }

    // ---------------- METODI GET ----------------

    public function getIdSpesa(): int
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
