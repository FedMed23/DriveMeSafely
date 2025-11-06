<?php

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
 */

 class ECartaDiCredito implements JsonSerializable {
     /**
     * Numero della Carta
     * @var string
     */  
     private string $numeroCarta;

     /**
     * Nome del titolare della Carta
     * @var string
     */  
     private string $nomeTitolare;

     /**
     * Cognome del titolare della Carta
     * @var string
     */  
     private string $cognomeTitolare;

     /**
     * Data della scadenza della carta 
     * @var DateTimeImmutable
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
     /**
     * @return string nome del titolare carta di credito
     */
    public function getNomeTitolareCarta(): string {
        return $this->nomeTitolare;
    }

    /**
     * @return string cognome del titolare carta di credito
     */
    public function getCognomeTitolareCarta(): string {
        return $this->cognomeTitolare;
    }

    /**
     * @return \DateTimeImmutable Data (Mese/Anno) di scadenza della carta di credito
     */
    public function getDataScadenza(): \DateTimeImmutable {
        return $this->dataScadenza;
    }

     /**
     * @return string numero della carta di credito
     */
    public function getNumeroCartaMascherato(): string {
        $numeroCartaMascherato = 'XXXX-XXXX-XXXX-' . substr($this->numeroCarta, -4);
        return $numeroCartaMascherato;
    }

   // ---------------------------- METODI SET ----------------------------

    /**
     * Imposta il nome del titolare della carta
     * @param string $nomeTitolare
     */
    public function setNomeTitolareCarta(string $nomeTitolare): void
    {
        $this->nomeTitolare = $nomeTitolare;
    }

    /**
     * Imposta il cognome del titolare della carta
     * @param string $cognomeTitolare
     */
    public function setCognomeTitolareCarta(string $cognomeTitolare): void
    {
        $this->cognomeTitolare = $cognomeTitolare;
    }

    /**
     * Imposta la data di scadenza della carta
     * @param \DateTimeImmutable $dataScadenza
     */
    public function setDataScadenza(\DateTimeImmutable $dataScadenza): void
    {
        $this->dataScadenza = $dataScadenza;
    }

    /**
     * Imposta il numero della carta di credito
     * @param string $numeroCarta
     */
    public function setNumeroCarta(string $numeroCarta): void
    {
        $this->numeroCarta = $numeroCarta;
    }

// ------------------ TOSTRING ---------------------------
    /**
    * Stampa i dettagli della carta di credito
    * @return string
    */
    public function __toString(): string
    {
        // Restituisce una stringa che include i dettagli chiave
        $dataFormattata = $this->dataScadenza->format('m-Y');
        
        return "Nome Titolare: {$this->nomeTitolare}\n Cognome Titolare:{$this->cognomeTitolare}\n Data Scadenza: {$dataFormattata}\n Numero Carta: {$this->getNumeroCartaMascherato()}\n";

    }
// --- Implementazione per la serializzazione JSON ---

    public function jsonSerialize(): array
    {
        return [
            'nomeTitolare' => $this->nomeTitolare,
            'cognomeTitolare' => $this->cognomeTitolare,
            // Formatta l'oggetto DateTimeImmutable in una stringa leggibile
            'dataScadenza' => $this->dataScadenza->format('m-Y'),
            'numeroCartaMascherato'=> $this->getNumeroCartaMascherato(),
            ];
    }
 
 ?>
