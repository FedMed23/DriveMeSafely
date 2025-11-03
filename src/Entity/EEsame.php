<?php

/**
 * La classe EEsame contiene le proprietà e gli attributi riguardanti un esame di guida.
 * Gli attributi che la descrivono sono:
 * - idEsame: identificativo univoco dell'esame
 * - tipologia: tipo di esame (es. teorico o pratico)
 * 
 * @access public
 * @author Camasso-Medelago
 * @package Entity
 */

class EEsame implements JsonSerializable
{
    /**
     * Identificativo univoco dell'esame
     * @var int
     */
    private int $idEsame;

    /**
     * Tipologia dell'esame (es. teorico o pratico)
     * @var string
     */
    private string $tipologia;


  // ---------------- METODI GET ----------------

    /**
     * Restituisce l'ID dell'esame
     * @return int
     */
    public function getIdEsame(): int
    {
        return $this->idEsame;
    }

    /**
     * Restituisce la tipologia dell'esame
     * @return string
     */
    public function getTipologia(): string
    {
        return $this->tipologia;
    }

  // ---------------- METODI SET ----------------

    /**
     * Imposta l'ID dell'esame
     * @param int $idEsame
     */
    public function setIdEsame(int $idEsame): void
    {
        $this->idEsame = $idEsame;
    }

    /**
     * Imposta la tipologia dell'esame
     * @param string $tipologia
     */
    public function setTipologia(string $tipologia): void
    {
        $this->tipologia = $tipologia;
    }

   // ------------------ TOSTRING ---------------------------

    /**
     * Stampa i dettagli dell'esame
     * @return string
     */
    public function __toString(): string
    {
        return "ID Esame: {$this->idEsame}\nTipologia: {$this->tipologia}\n";
    }

    // --- Implementazione per la serializzazione JSON ---

    /**
     * Serializza l'oggetto in formato JSON
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'idEsame' => $this->idEsame,
            'tipologia' => $this->tipologia
        ];
    }
}

?>

