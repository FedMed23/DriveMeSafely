<?php

/**
 * La classe EQuiz rappresenta un set di domande.
 * Contiene una collezione di oggetti EDomanda.
 */
class EQuiz implements JsonSerializable {

    private ?int $idQuiz = null; 
    private string $nomeQuiz; 
    private array $domande = []; // Array di oggetti EDomanda

    //-------------------------COSTRUTTORE-------------------------

    public function __construct(string $_nome, array $_domandeIniziali = []) {
        $this->nomeQuiz = $_nome;
        
        // Verifica dell'array 
        foreach ($_domandeIniziali as $domanda) {
            if (!($domanda instanceof EDomanda)) {
                throw new \InvalidArgumentException("L'array del quiz deve contenere solo oggetti EDomanda.");
            }
            $this->domande[] = $domanda;
        }
    }

    //----------------------METODI GET/SET (ID)-----------------------------
    
    public function getId(): ?int { return $this->idQuiz; }
    public function setId(int $id): void { $this->idQuiz= $id; } 

    //----------------------METODI GET -----------------------------

    public function getNomeQuiz(): string { return $this->nomeQuiz; }

    /**
     * @return EDomanda[] Restituisce l'array di domande
     */
    public function getDomande(): array {
        return $this->domande;
    }

   //-----------------------------METODI SET-----------------------------

    public function setNomeQuiz(string $nome): void { $this->nomeQuiz = $nome; }
    
    //----------------------Altri metodi -----------------------------
    
    /**
     * Aggiunge una domanda al quiz.
     */
    public function addDomanda(EDomanda $domanda): void {
        $this->domande[] = $domanda;
    }

    /**
     * Rimuove una domanda dal quiz.
     */
    public function removeDomanda(EDomanda $domanda): void {

        $key = array_search($domanda, $this->domande, true);
        if ($key !== false) {
            unset($this->domande[$key]);
            $this->domande = array_values($this->domande); // Riorganizza gli indici
        }
    }

    //---------------------JSON-------------------------------

    public function jsonSerialize(): array {
        return [
            'id' => $this->idQuiz,
            'nomeQuiz' => $this->nomeQuiz,
            // Serializzazione ID 
            'domandeId' => array_map(fn($d) => $d->getId(), $this->domande)
        ];
    }
    //--------------------METODO TOSTRING--------------

    /**
     * Stampa i dettagli del quiz.
     * @return string
     */
    public function __toString(): string  {
        $idStr = $this->getId() === null ? "[NUOVO]" : $this->getId();
        $print =" Nome Quiz: ".$this->getNomeQuiz()."\n"." Id domande : ".$idStr."\n".;
        
        return $print;
    }
}