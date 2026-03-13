<?php
namespace Entity;
use Doctrine\ORM\Mapping as ORM;

/** 
*La classe ECartaDiCredito contiene le proprietà e gli attributi riguardanti una carta di credito
* Gli attributi che la descrivono sono:
 * - nomeTitolare: nome del titolare della carta di credito
 * - cognomeTitolare: cognome del titolare della carta di credito
 * - dataScadenza: data di scadenza della carta di credito
 * - numeroCarta: numero della carta di credito
 * @access public
 * @author Camasso-Medelago
 * @package Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="carta_di_credito")
 */
class ECartaDiCredito implements \JsonSerializable {

    /**
     * Numero della Carta (chiave primaria)
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string", length=16)
     */  
    private string $numeroCarta;

    /**
     * Nome del titolare della Carta
     * @var string
     * @ORM\Column(type="string", length=100)
     */  
    private string $nomeTitolare;

    /**
     * Cognome del titolare della Carta
     * @var string
     * @ORM\Column(type="string", length=100)
     */  
    private string $cognomeTitolare;

    /**
     * Data della scadenza della carta 
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */  
    private DateTimeImmutable $dataScadenza;

     
// -----------------------------COSTRUTTORE-----------------
    /** 
     * Crea una nuova istanza della classe ECartaDiCredito
     * @param string $nomeTitolare  nome del titolare
     * @param string $cognomeTitolare  cognome del titolare
     * @param \DateTimeImmutable $dataScadenza data scadenza carta di credito
     * @param string $numeroCarta numero della carta di credito
     */
    public function __construct(string $nomeTitolare, string $cognomeTitolare, DateTimeImmutable $dataScadenza, string $numeroCarta) {

       //Algoritmo che cripta il numero della carta da implementare(?)

       $this->nomeTitolare= $nomeTitolare;
       $this->cognomeTitolare= $cognomeTitolare;
       $this->dataScadenza= $dataScadenza;
       $this->numeroCarta= $numeroCarta;
    }
// ---------------------------- METODI GET ------------------------
    public function getNomeTitolareCarta(): string {
       return $this->nomeTitolare;
    }

    public function getCognomeTitolareCarta(): string {
       return $this->cognomeTitolare;
    }

    public function getDataScadenza(): \DateTimeImmutable {
       return $this->dataScadenza;
    }

    public function getNumeroCartaMascherato(): string {
       $numeroCartaMascherato = 'XXXX-XXXX-XXXX-' . substr($this->numeroCarta, -4);
       return $numeroCartaMascherato;
    }

  // ---------------------------- METODI SET ----------------------------
    public function setNomeTitolareCarta(string $nomeTitolare): void
    {
       $this->nomeTitolare = $nomeTitolare;
    }

    public function setCognomeTitolareCarta(string $cognomeTitolare): void
    {
       $this->cognomeTitolare = $cognomeTitolare;
    }

    public function setDataScadenza(\DateTimeImmutable $dataScadenza): void
    {
       $this->dataScadenza = $dataScadenza;
    }

    public function setNumeroCarta(string $numeroCarta): void
    {
       $this->numeroCarta = $numeroCarta;
    }

// ------------------ TOSTRING ---------------------------
    public function __toString(): string
    {
       $dataFormattata = $this->dataScadenza->format('m-Y');
       return "Nome Titolare: {$this->nomeTitolare}\n Cognome Titolare:{$this->cognomeTitolare}\n Data Scadenza: {$dataFormattata}\n Numero Carta: {$this->getNumeroCartaMascherato()}\n";
    }

// --- Implementazione per la serializzazione JSON ---
    public function jsonSerialize(): array
    {
       return [
           'nomeTitolare' => $this->nomeTitolare,
           'cognomeTitolare' => $this->cognomeTitolare,
           'dataScadenza' => $this->dataScadenza->format('m-Y'),
           'numeroCartaMascherato'=> $this->getNumeroCartaMascherato(),
       ];
    }

}
?>
