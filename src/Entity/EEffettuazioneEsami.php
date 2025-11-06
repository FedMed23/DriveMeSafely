<?php
/**
*La classe EEffettuazioneEsami rappresenta lo svolgimento dell'esame da parte dell'utente iscritto alla scuola guida.
*/

class EEffettuazioneEsami implements JsonSerializable {

    private ?int $idEffEs = null;
    private EEsame $esame;                        // L'esame effettuato
    private int $idIscritto;          // L'iscritto che ha fatto la prova
    private int $tentativi;      // Tentativi svolgimento esami
    private bool $superato;                     // Esito finale (True/False)

    //-------------------------COSTRUTTORE-------------------------

    public function __construct(EEsame $esame, EIscritto $_iscritto, int $_tentativi, bool $_superato) {
        $this->esame = $esame;
        $this->idIscritto = $_iscritto.getId();
        $this->tentativi = $_tentativi;
        $this->superato = $_superato;
    }
    
    //----------------------METODI GET/SET (ID)-----------------------------
    
    public function getId(): ?int { return $this->idEffEs; }
    public function setId(int $id): void { $this->idEffEs = $id; } 

    //----------------------METODI GET-----------------------------
    
    public function getEsame(): int { return $this->esame; }
    public function getIdIscritto(): int { return $this->idIscritto; }
    public function getTentativi(): int { return $this->tentativi; }
    public function isSuperato(): bool { return $this->superato; }

// ---------------- METODI SET ----------------

    public function setIscritto(EIscritto $idIscritto): void
    {
        $this->idIscritto= $idIscritto.getId();
    }

    public function setEsame(EEsame $Esame): void
    {
        $this->esame = $esame;
    }
    
    public function setTentativi(int $tentativi): void
    {
        $this->tentativi = $tentativi;
    }

    public function isSuperato(bool $superato): void
    {
        $this->superato = $superato;
    }
    //---------------------JSON-------------------------------

    public function jsonSerialize(): array {
        return [
            'idEffEs' => $this->idEffEs,
            'Esame' => $this->esame,
            'iscrittoId' => $this->idIscritto,
            'tentativi' => $this->tentativi,
            'superato' => $this->superato,
        ];
    }

//--------------------METODO TOSTRING--------------

/**
 * Stampa i dettagli dello svolgimento del quiz.
 * @return string
 */
public function __toString(): string
    {
        return "idEffettuazioneEsame: {$this->getId()}\nIscritto: {$this->idIscritto}\n Esame: {$this->esame}\nTentativi: {$this->tentativi}\nSuperato: {$this->superato}\n";
    }
}
?>