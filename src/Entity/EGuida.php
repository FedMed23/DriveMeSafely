<?php

/** 
*La classe EGuida contiene le proprietà e gli attributi riguardanti una guida in scuolaguida
* Gli attributi che la descrivono sono:
 * - numeroGuida: un numero che indica il numero della guida
 * - dataOra: data e ora in cui si svolge la guida
 * @access public
 * @author Camasso-Medelago
 * @package Entity
 */

 class EGuida implements JsonSerializable {
     /**
     * Numero della guida
     * @var int
     */  
     private int $numeroGuida;

     /**
     * Data e ora della guida 
     * @var DateTimeImmutable
     */  
     private DateTimeImmutable $dataOra;

// -----------------------------COSTRUTTORE-----------------
     /** 
      * Crea una nuova istanza della classe EGuida
     * @param int $numeroGuida L'ID della guida.
     * @param \DateTimeImmutable $dataOra L'orario pianificato della guida
     */
     public function __construct(int $numeroGuida, DateTimeImmutable $dataOra) {

        $this->numeroGuida= $numeroGuida;
        $this->dataOra= $dataOra;
     }
// ---------------------------- METODI GET ------------------------
     /**
     * @return int numero della guida
     */
    public function getNumeroGuida(): int {
        return $this->numeroGuida;
    }

    /**
     * @return \DateTimeImmutable Data e Ora della guida
     */
    public function getDataOra(): \DateTimeImmutable {
        return $this->dataOra;
    }
// ------------------ METODI SET ---------------------------
    /**
     * @param int $numeroGuida della guida
     */
    public function setNumeroGuida(int $numeroGuida): void  {
        $this->numeroGuida=$numeroGuida;
    }
    /**
     * @param \DateTimeImmutable $dataOra L'orario pianificato della guida
     */
    public function setDataOra(DateTimeImmutable $dataOra): void  {
        $this->dataOra=$dataOra;
    }

// ------------------ TOSTRING ---------------------------
    /**
    * Stampa i dettagli della guida
    * @return string
    */
    public function __toString(): string
    {
        // Restituisce una stringa che include i dettagli chiave
        $dataFormattata = $this->dataOra->format('d/m/Y H:i');
        
        return "Guida numero: {$this->numeroGuida} programmata per il: {$dataFormattata}";

    }
// --- Implementazione per la serializzazione JSON ---

    public function jsonSerialize(): array
    {
        return [
            'numeroGuida' => $this->numeroGuida,
            // Formatta l'oggetto DateTimeImmutable in una stringa leggibile
            'dataOra' => $this->dataOra->format('Y-m-d H:i:s'), 
        ];
    }
 
 ?>
