<?php
/**
*La classe ETentativoQuiz rappresenta lo svolgimento del quiz da parte dell'utente iscritto 
*alla scuola guida.
*/

class ESvolgimentoQuiz implements JsonSerializable {

    private ?int $idSvolgimento = null;
    private EQuiz $quiz;                        // L'oggetto Quiz effettuato
    private EIscritto $iscritto;          // L'iscritto che ha fatto la prova
    private \DateTimeImmutable $dataSvolgimento; // Data e ora di esecuzione
    private int $errori = 0;                    // Numero di risposte errate
    private array $tentativiRisposta = [];      // Array di oggetti ETentativoRisposta
    private bool $superato;                     // Esito finale (True/False)

    //-------------------------COSTRUTTORE-------------------------

    public function __construct(EQuiz $_quiz, EIscritto $_iscritto, array $_risposteUtente) {
        $this->quiz = $_quiz;
        $this->iscritto = $_iscritto;
        $this->dataSvolgimento = new \DateTimeImmutable(); // Registra l'ora della creazione
        
        // Elabora le risposte fornite per creare gli oggetti ETentativoRisposta
        $this->elaboraRisposte($_risposteUtente);
        
        // Calcola l'esito finale
        $this->calcolaEsito();
    }
    
    //----------------------Elaborazione risposte e calcolo errori-----------------------------

    /**
     * Elabora l'array di risposte e crea gli oggetti ETentativoRisposta.
     * @param array $_risposteUtente Array associativo [ID_Domanda => 'Risposta Utente']
     */
    private function elaboraRisposte(array $_risposteUtente): void {
        
        // Cerca le domande del quiz per ID per un accesso più rapido
        $domandePerId = [];
        foreach ($this->quiz->getDomande() as $domanda) {
            $domandePerId[$domanda->getId()] = $domanda;
        }
        
        //Per ogni risposta dell'utente associata ad una domanda, ricava la risposta e insieme alla domanda viene passata al costruttore del TentativoRisposta
        foreach ($_risposteUtente as $domandaId => $risposta) {
            if (isset($domandePerId[$domandaId])) {
                $domanda = $domandePerId[$domandaId];
                
                // Crea l'oggetto TentativoRisposta
                $tentativo = new ETentativoRisposta($domanda, $risposta);
                $this->tentativiRisposta[] = $tentativo;
                
                //Inizializza l'attributo errori
                if (!$tentativo->isCorretta()) {
                    $this->errori++;
                }
            }
        }
    }
    
    /**
     * Determina se il quiz è superato (massimo 4 errori).
     */
    private function calcolaEsito(): void {
        $this->superato = ($this->errori <= 4); 
    }
    
    //----------------------METODI GET/SET (ID)-----------------------------
    
    public function getId(): ?int { return $this->idSvolgimento; }
    public function setId(int $id): void { $this->idSvolgimento = $id; } 

    //----------------------METODI GET-----------------------------
    
    public function getQuiz(): EQuiz { return $this->quiz; }
    public function getIscritto(): EUtenteIscritto { return $this->iscritto; }
    public function getDataSvolgimento(): \DateTimeImmutable { return $this->dataSvolgimento; }
    public function getErrori(): int { return $this->errori; }
    public function isSuperato(): bool { return $this->superato; }
    
    /**
     * @return ETentativoRisposta[] La collezione di tutte le risposte date.
     */
    public function getTentativiRisposta(): array { return $this->tentativiRisposta; }

    //---------------------JSON-------------------------------

    public function jsonSerialize(): array {
        return [
            'idSvolgimento' => $this->idSvolgimento,
            'quizId' => $this->quiz->getId(),
            'iscrittoId' => $this->iscritto->getId(),
            'dataSvolgimento' => $this->dataSvolgimento->format('Y-m-d H:i:s'),
            'errori' => $this->errori,
            'superato' => $this->superato,
            'risposte' => $this->tentativiRisposta // Serializza l'array dei tentativi
        ];
    }
    // ... (I metodi precedenti sono corretti e omessi per brevità) ...

//--------------------METODO TOSTRING--------------

/**
 * Stampa i dettagli dello svolgimento del quiz.
 * @return string
 */
public function __toString(): string {
    $idStr = $this->getId() === null ? "[NUOVO]" : $this->getId();
    $esito = $this->isSuperato() ? "SUPERATO" : "BOCCIATO";

    $print = "=== SVOLGIMENTO QUIZ ID: {$idStr} ===\n";
    $print .= "Quiz: {$this->getQuiz()->getNomeQuiz()} (ID: {$this->getQuiz()->getId()})\n";
    $print .= "Iscritto ID: {$this->getIscritto()->getId()}\n";
    $print .= "Data Svolgimento: {$this->getDataSvolgimento()->format('Y-m-d H:i:s')}\n";
    $print .= "---------------------------------------\n";
    $print .= "Errori Totali: {$this->getErrori()}\n";
    $print .= "Esito Finale: **{$esito}**\n";
    $print .= "=======================================\n";
    
    return $print;
}
?>