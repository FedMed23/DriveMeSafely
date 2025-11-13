<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * La classe EEsame contiene le proprietà e gli attributi riguardanti un esame di guida.
 * Gli attributi che la descrivono sono:
 * - idEsame: identificativo univoco dell'esame
 *  - data: data dell'esame
 * - tipologia: tipo di esame (es. teorico o pratico)
 * 
 * @access public
 * @author Camasso-Medelago
 * @package Entity
 * @ORM\Entity
 * @ORM\Table(name="esame")
 */

class EEsame implements JsonSerializable
{
    /**
     * Identificativo univoco dell'esame (chiave primaria)
     * @var int|null
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="int", length=100)
     */
    private ?int $idEsame = null;

    /**
     * Data dell'esame
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */  
    private DateTimeImmutable $dataEs;

    /**
     * Tipologia dell'esame (es. teorico o pratico)
     * @var string
     * @ORM\Column(type="string", length=7)  
     */
    private string $tipologia;

// ---------------- COSTRUTTORE ----------------

    /**
     * Crea una nuova istanza della classe EEsame
     *
     * @param string $tipologia tipo di esame
     * @param DateTimeImmutable $dataEs Data in cui si svolge l'esame
     */
    public function __construct(string $tipologia, DateTimeImmutable $dataEs)
    {
        $this->tipologia = $tipologia;
        $this->dataEs = $dataEs; 
    }

  // ---------------- METODI GET ----------------

    /**
     * Restituisce l'ID dell'esame
     * @return int|null
     */
    public function getIdEsame(): ?int
    {
        return $this->idEsame;
    }

    public function getDataEsame(): DateTimeImmutable
    {
        return $this->dataEs;
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

    public function setDataEsame(DateTimeImmutable $data): void
    {
        $this->dataEs = $data;
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
        $dataFormattata = $this->dataEs->format('d-m-Y');
        $id = $this->idEsame ?? 'N/D';
        return "ID Esame: {$id}\nData: {$dataFormattata}\nTipologia: {$this->tipologia}\n";
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
            'data' => $this->dataEs->format('Y-m-d'),
            'tipologia' => $this->tipologia
        ];
    }
}

?>

