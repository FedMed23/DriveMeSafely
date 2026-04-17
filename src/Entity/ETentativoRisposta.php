<?php

namespace CamassoMedelago\DriveMeSafely\Entity;
use Doctrine\ORM\Mapping as ORM;


/**
 * La classe ETentativoRisposta rappresenta una singola risposta data dall'utente 
 * ad una domanda durante lo svolgimento di un quiz.
 * Gli attributi che la descrivolo sono:
 * -idTent: id tentativo risposta
 * -domanda: oggetto della classe domanda
 * -rispostaUtente: risposta dell'utente che può essere True o False
 * -esito: riporta se la risposta dell'utente alla domanda è giusta o sbagliata (True o False)
 *  @author Camasso-Medelago
 *  @package Entity
 * @ORM\Entity
 * @ORM\Table(name="tentativo_risposta")
 */
class ETentativoRisposta implements \JsonSerializable {
    /**
     * Identificativo univoco del tentativo (chiave primaria)
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private ?int $idTent = null;

    /**
     * Domanda associata alla risposta
     * @var EDomanda
     * @ORM\ManyToOne(targetEntity="EDomanda")
     * @ORM\JoinColumn(name="domanda_id", referencedColumnName="idDomanda", nullable=false)
     */
    private EDomanda $domanda;    
    
    /**
     * @ORM\ManyToOne(targetEntity="ESvolgimentoQuiz", inversedBy="tentativiRisposta")
     * @ORM\JoinColumn(name="svolgimento_id", referencedColumnName="idSvolgimento", nullable=false)
     */
    private ESvolgimentoQuiz $svolgimento;
    
     /**
     * Risposta dell'utente (V o F)
     * @var bool
     * @ORM\Column(type="boolean")  
     */
    private bool $rispostaUtente;   
     /**
     * Esito della risposta (true se giusta, false se sbagliata)
     * @var bool
     * @ORM\Column(type="boolean")  
     */
    private bool $esito;             

//-------------------------COSTRUTTORE-------------------------

    public function __construct( ESvolgimentoQuiz $svolgimento, EDomanda $domanda, bool $rispostaUtente) {
        $this->svolgimento = $svolgimento;
        $this->domanda = $domanda;
        $this->rispostaUtente = $rispostaUtente;
        $this->esito = ($rispostaUtente === $domanda->getRispostaCorretta());
    }
 
 //----------------------METODI GET-----------------------------   
    public function getId(): ?int {
        return $this->idTent;
    }
    public function getDomanda(): EDomanda { return $this->domanda; }
    public function getSvolgimento(): ESvolgimentoQuiz { return $this->svolgimento; }
    public function getRispostaUtente(): bool { return $this->rispostaUtente; }
    public function isCorretta(): bool { return $this->esito; }

//---------------------JSON-------------------------------
    public function jsonSerialize(): array {
        return [
            'idTent' => $this->idTent,
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
        $print ="idTent: ".$this->idTent."\n".
                " idDomanda: ".$this->domanda->getId()."\n".
                " rispostaUtente: ".$rispostaUtenteStr."\n".
                 "esito : ".$esitoStr."\n";
        
        return $print;
    }
}
?>


