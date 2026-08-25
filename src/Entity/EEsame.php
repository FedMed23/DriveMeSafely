<?php

namespace CamassoMedelago\DriveMeSafely\Entity;
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

class EEsame implements \JsonSerializable
{
    /**
     * Identificativo univoco dell'esame
     *
     * @var int|null
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id_esame", type="integer")
     */
    private ?int $idEsame = null;

    /**
     * Data e ora dell'esame
     *
     * @var DateTime
     * @ORM\Column(name="data_es", type="datetime", nullable=false)
     */
    private DateTime $dataEs;

    /**
     * Tipologia dell'esame
     *
     * @var TipologiaEsame
     * @ORM\Column(name="tipologia", type="string", length=15, nullable=false)
     */
    private TipologiaEsame $tipologia;


    // ---------------- COSTRUTTORI ----------------

    /**
     * Costruttore vuoto obbligatorio per Doctrine.
     */
    public function __construct()
    {
    }

    /**
     * Crea una nuova istanza della classe EEsame.
     *
     * @param TipologiaEsame $tipologia tipo di esame
     * @param DateTime $dataEs data e ora in cui si svolge l'esame
     */
    public function init(
        TipologiaEsame $tipologia,
        DateTime $dataEs
    ): void {
        $this->tipologia = $tipologia;
        $this->dataEs = $dataEs;
    }


    // ---------------- METODI GET ----------------

    public function getIdEsame(): ?int
    {
        return $this->idEsame;
    }

    public function getDataEs(): DateTime
    {
        return $this->dataEs;
    }

    public function getTipologia(): TipologiaEsame
    {
        return $this->tipologia;
    }


    // ---------------- METODI SET ----------------

    public function setIdEsame(?int $idEsame): void
    {
        $this->idEsame = $idEsame;
    }

    public function setDataEsame(DateTime $dataEs): void
    {
        $this->dataEs = $dataEs;
    }

    public function setTipologia(TipologiaEsame $tipologia): void
    {
        $this->tipologia = $tipologia;
    }


    // ---------------- DATA FORMATTATA ----------------

    public function getDataOraFormattata(): string
    {
        if (!isset($this->dataEs)) {
            return "";
        }

        return $this->dataEs->format('d/m/Y H:i');
    }


    // ------------------ TOSTRING ---------------------------

    public function __toString(): string
    {
        return "Esame ID: " .
            $this->idEsame .
            " | Tipo: " .
            $this->tipologia .
            " | Data: " .
            $this->dataEs->format('Y-m-d H:i:s');
    }


    // ------------------ JSON ---------------------------

    public function jsonSerialize(): array
    {
        return [
            'idEsame' => $this->idEsame,
            'dataEs' => $this->dataEs->format('Y-m-d H:i:s'),
            'tipologia' => $this->tipologia
        ];
    }
}

?>
