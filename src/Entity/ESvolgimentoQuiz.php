<?php
namespace Entity;
use Entity\EQuiz;
use Entity\EIscritto;
use Entity\ETentativoRisposta;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
*La classe ESvolgimentoQuiz rappresenta lo svolgimento del quiz da parte dell'utente iscritto 
*alla scuola guida.
*Gli attributi che la descrivono sono:
* -idSvolgimento: id dello svolgimento del quiz
* -quiz: oggetto della classe EQuiz
* -idIscritto: l'id dell'iscritto che ha effettuato il quiz
* -dataSvolgimento: data e ora dello svolgimento del quiz
* -errori: errori commessi nel quiz
* -tentativiRisposta: array delle risposte dell'utente alle domande del quiz
* -superato: riporta se il quiz è statu superato o meno (True/False)
* @access public
* @package Entity
* @author Camasso-Medelago
* @ORM\Entity
* @ORM\Table(name="svolgimento_quiz")
*/

class ESvolgimentoQuiz implements \JsonSerializable {
    /**
     * id identificativo dello svolgimento del quiz
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * */
    private ?int $idSvolgimento = null;
    /**
     * Quiz svolto
     * @ORM\ManyToOne(targetEntity="EQuiz")
     * @ORM\JoinColumn(name="quiz_id", referencedColumnName="idQuiz", nullable=false)
     */
    private EQuiz $quiz;                       
    /**
     * id identificativo dell'iscritto che svolge il quiz
     * @var int
     * @ORM\Column(type="integer")
     * */
    private int $idIscritto;                      
    /**
     * Data e ora dello svolgimento del quiz
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeImmutable $dataSvolgimento; 
    /**
     * numero errori fatti nel quiz
     * @var int
     * @ORM\Column(type="integer")
     * */
    private int $errori = 0;      
    /**
    * Array di tentativi di risposte
    **
    * @ORM\OneToMany(targetEntity="ETentativoRisposta", mappedBy="svolgimento", cascade={"persist", "remove"})
    * @var array
    */
    private array $tentativiRisposta = [];     
    /**
     * esito finale del quiz (True o False)
     * @var bool
     * @ORM\Column(type="boolean")
     * */
    private bool $superato;                     

    //-------------------------COSTRUTTORE-------------------------

    public function __construct(EQuiz $quiz, EIscritto $iscritto, array $risposteUtente) {
        $this->quiz = $quiz;
        $this->idIscritto = $iscritto->getId();
        $this->dataSvolgimento = new \DateTimeImmutable(); // Registra l'ora della creazione
        
        // Elabora le risposte fornite per creare gli oggetti ETentativoRisposta
        $this->elaboraRisposte($risposteUtente);
        
        // Calcola l'esito finale
        $this->calcolaEsito();
    }
    
    //----------------------Elaborazione risposte e calcolo errori-----------------------------

    /**
     * Elabora l'array di risposte e crea gli oggetti ETentativoRisposta.
     * @param array $risposteUtente Array associativo [ID_Domanda => 'Risposta Utente']
     */
    private function elaboraRisposte(array $risposteUtente): void {
        
        // Cerca le domande del quiz per ID per un accesso più rapido
        $domandePerId = [];
        foreach ($this->quiz->getDomande() as $domanda) {
            $domandePerId[$domanda->getId()] = $domanda;
        }
        
        //Per ogni risposta dell'utente associata ad una domanda, ricava la risposta e insieme alla domanda viene passata al costruttore del TentativoRisposta
        foreach ($risposteUtente as $domandaId => $risposta) {
            if (isset($domandePerId[$domandaId])) {
                $domanda = $domandePerId[$domandaId];
                
                // Crea l'oggetto TentativoRisposta
                $tentativo = new ETentativoRisposta($this, $domanda, $risposta);
                $this->tentativiRisposta[] = $tentativo;
                
                //Inizializza l'attributo errori
                if (!$tentativo->isCorretta()) {
                    $this->errori++;
                }
            }
        }
    }
    
    /**
     * Determina se il quiz è superato (massimo 3 errori).
     */
    private function calcolaEsito(): void {
        $this->superato = ($this->errori <= 3); 
    }
    
    //----------------------METODI GET/SET (ID)-----------------------------
    
    public function getId(): ?int { return $this->idSvolgimento; }
    
    //----------------------METODI GET-----------------------------
    
    public function getQuiz(): EQuiz { return $this->quiz; }
    public function getIdIscritto(): int { return $this->idIscritto; }
    public function getDataSvolgimento(): \DateTimeImmutable { return $this->dataSvolgimento; }
    public function getErrori(): int { return $this->errori; }
    public function isSuperato(): bool { return $this->superato; }
    
    /**
     * @return ETentativoRisposta[] La collezione di tutte le risposte date.
     */
    public function getTentativiRisposta(): array { return $this->tentativiRisposta; }

    //----------------------METODI SET-----------------------------
    
    public function setQuiz(EQuiz $quiz): void { $this->quiz= $quiz; }
    public function setIdIscritto(EIscritto $iscritto): void { $this->idIscritto= $iscritto->getId(); }
    public function setDataSvolgimento(\DateTimeImmutable $data): void { $this->dataSvolgimento= $data; }
    public function setErrori(int $errori): void { $this->errori= $errori; }
    public function setSuperato(bool $superato): void { $this->superato= $superato; }
    

    //---------------------JSON-------------------------------

    public function jsonSerialize(): array {
        return [
            'idSvolgimento' => $this->idSvolgimento,
            'quizId' => $this->quiz->getId(),
            'iscrittoId' => $this->idIscritto,
            'dataSvolgimento' => $this->dataSvolgimento->format('Y-m-d H:i:s'),
            'errori' => $this->errori,
            'superato' => $this->superato,
            'risposte' => $this->tentativiRisposta // Serializza l'array dei tentativi
        ];
    }

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
    $print .= "Iscritto ID: {$this->getIdIscritto()}\n";
    $print .= "Data Svolgimento: {$this->getDataSvolgimento()->format('Y-m-d H:i:s')}\n";
    $print .= "---------------------------------------\n";
    $print .= "Errori Totali: {$this->getErrori()}\n";
    $print .= "Esito Finale: **{$esito}**\n";
    $print .= "=======================================\n";
    
    return $print;
 }
}
?>
