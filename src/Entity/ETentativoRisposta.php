<?php
/**
 * La classe ETentativoRisposta rappresenta una singola risposta data dall'utente 
 * ad una domanda durante lo svolgimento di un quiz.
 * Gli attributi che la descrivolo sono:
 * -domanda: oggetto della classe domanda
 * -rispostaUtente: risposta dell'utente che può essere True o False
 * -esito: riporta se la risposta dell'utente alla domanda è giusta o sbagliata (True o False)
 *  @author Camasso-Medelago
 *  @package Entity
 */
class ETentativoRisposta implements JsonSerializable {
    
    private EDomanda $domanda;         
    private bool $rispostaUtente;   // V o F
    private bool $esito;             

//-------------------------COSTRUTTORE-------------------------

    public function __construct(EDomanda $domanda, bool $rispostaUtente) {
        $this->domanda = $domanda;
        $this->rispostaUtente = $rispostaUtente;
        $this->esito = ($rispostaUtente === $domanda->getRispostaCorretta());
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
        $print =" idDomanda: ".$this->domanda->getId()."\n".
                " rispostaUtente: ".$rispostaUtenteStr."\n".
                 "esito : ".$esitoStr."\n";
        
        return $print;
    }
}
?>


