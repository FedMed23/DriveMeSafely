<?php

/**
 * La classe EDomanda rappresenta il contenuto di una domanda e la sua risposta corretta.
 */
class EDomanda implements JsonSerializable {

    private ?int $idDomanda= null; 
    private string $contenuto; // Testo della domanda 
    private bool $rispostaCorretta; // True, False

    //-------------------------COSTRUTTORE-------------------------

    public function __construct(string $_contenuto, bool $_risposta) {
        $this->contenuto = $_contenuto;
        $this->rispostaCorretta = $_risposta;
    }

    //----------------------METODI GET/SET (ID)-----------------------------
    
    public function getId(): ?int { return $this->idDomanda; }
    public function setId(int $id): void { $this->idDomanda = $id; } 

    //----------------------METODI GET-----------------------------

    public function getContenuto(): string { return $this->contenuto; }
    public function getRispostaCorretta(): bool { return $this->rispostaCorretta; }

    //-----------------------------METODI SET-----------------------------

    public function setContenuto(string $contenuto): void { $this->contenuto = $contenuto; }
    public function setRispostaCorretta(bool $risposta): void { $this->rispostaCorretta = $risposta; }
    

    //--------------------TOSTRING--------------

    /**
     * Stampa i dettagli della domanda.
     * @return string
     */
    public function __toString(): string  {
        $print =" idDomanda: ".$this->getId()."\n"." Contenuto : ".$this->getContenuto()."\n".
        "rispostaCorretta : ".$this->getRispostaCorretta()."\n";
        
        return $print;
    }
     //---------------------IMPLEMENTAZIONE JSON-------------------------------

    public function jsonSerialize(): array {
        return [
            'idDomanda' => $this->idDomanda,
            'contenuto' => $this->contenuto,
            'rispostaCorretta' => $this->rispostaCorretta
        ];
    }
}
