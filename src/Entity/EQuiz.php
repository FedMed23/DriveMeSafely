<?php

namespace CamassoMedelago\DriveMeSafely\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * La classe EQuiz rappresenta un set di domande.
 * Gli attributi che la definiscono sono:
 * -idQuiz: l'id del Quiz
 * -nomeQuiz: il nome del quiz
 * -domande: l'array di domande che compongono il quiz
 *
 * @access public
 * @package Entity
 * @author Camasso-Medelago
 * @ORM\Entity
 * @ORM\Table(name="quiz")
 */
class EQuiz implements \JsonSerializable {
    /**
     * id identificativo del quiz
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * */
    private ?int $idQuiz = null; 
    
    /**
     * Nome del quiz
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    private string $nomeQuiz; 
    /**
     * Insieme di domande del quiz
     * @var array
     * @ORM\ManyToMany(targetEntity="EDomanda", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="quiz_domanda",
     *     joinColumns={@ORM\JoinColumn(name="quiz_id", referencedColumnName="idQuiz")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="domanda_id", referencedColumnName="idDomanda")}
     *)
     */
    private array $domande = []; 

    //-------------------------COSTRUTTORE-------------------------

    public function __construct(string $nome, array $domandeIniziali = []) {
        $this->nomeQuiz = $nome;
        
        // Verifica dell'array 
        foreach ($domandeIniziali as $domanda) {
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
            'idQuiz' => $this->idQuiz,
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
        $output = "idQuiz: {$this->idQuiz}\nNome Quiz: {$this->nomeQuiz}\n--- Domande ---\n";
        $numero=0;
        foreach ($this->domande as $domanda) {
            $numero++;
            $output .= "Domanda {$numero}:\n" .(string)$domanda . "\n";
        }
        return $output;
    }
}
?>
