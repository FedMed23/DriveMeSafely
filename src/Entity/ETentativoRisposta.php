<?php
/**
 * La classe ETentativoRisposta rappresenta una singola risposta data dall'utente 
 * ad una domanda durante lo svolgimento di un quiz.
 */
class ETentativoRisposta implements JsonSerializable {
    
    private EDomanda $domanda;         
    private bool $rispostaUtente;   // V o F
    private bool $esito;             

//-------------------------COSTRUTTORE-------------------------

    public function __construct(EDomanda $_domanda, bool $_rispostaUtente) {
        $this->domanda = $_domanda;
        $this->rispostaUtente = $_rispostaUtente;
        $this->esito = ($_rispostaUtente) === ($_domanda->getRispostaCorretta());
    }
 
 //----------------------METODI GET-----------------------------   
    public function getDomanda(): EDomanda { return $this->domanda; }
    public function getRispostaUtente(): bool { return $this->rispostaUtente; }
    public function isCorretta(): bool { return $this->esito; }

//---------------------JSON-------------------------------
    public function jsonSerialize(): array {
        return [
            'domandaId' => $this->domanda->getId(),
            'rispostaUtente' => $this->rispostaUtente,
            'esito' => $this->esito
        ];
    }

//--------------------METODO TOSTRING--------------

    /**
     * Stampa i dettagli del tentativo.
     * @return string
     */
    public function __toString(): string  {
        $rispostaUtenteStr = $this->rispostaUtente ? "Vero" : "Falso";
        $esitoStr = $this->esito ? "Corretta" : "Errata";
        $print =" idDomanda: ".$this->getId()."\n"." rispostaUtente: ".$rispostaUtenteStr."\n"."esito : ".$esitoStr."\n";
        
        return $print;
    }
}
?>


