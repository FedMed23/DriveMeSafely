<?php
namespace CamassoMedelago\DriveMeSafely\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * La classe EDomanda rappresenta il contenuto di una domanda del quiz e la sua risposta corretta.
 
 * @access public
 * @author Camasso-Medelago
 * @package Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="domanda")
 */
class EDomanda implements \JsonSerializable {
 /**
     * Identificativo univoco della domanda (può essere null se non ancora salvata su DB) (chiave primaria)
     * @var int|null
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private ?int $idDomanda = null;

    /**
     * Testo della domanda
     * @var string
     * @ORM\Column(type="string", length=100)  
     */
    private string $contenuto;

    /**
     * Indica se la risposta è corretta (true/false)
     * @var bool
     * @ORM\Column(type="boolean")
     */  
    private bool $rispostaCorretta;


    //-------------------------COSTRUTTORE-------------------------

     /**
     * Costruttore della classe EDomanda
     * 
     * @param string $_contenuto  Testo della domanda
     * @param bool $_risposta     Indica se la risposta è corretta
     */
    public function __construct(string $_contenuto, bool $_risposta)
    {
        $this->contenuto = $_contenuto;
        $this->rispostaCorretta = $_risposta;
    }

    //----------------------METODI GET/SET (ID)-----------------------------
    
    /**
     * Restituisce l'ID della domanda
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->idDomanda;
    }

    /**
     * Imposta l'ID della domanda
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->idDomanda = $id;
    }

    //----------------------METODI GET-----------------------------

     /**
     * Restituisce il testo della domanda
     * @return string
     */
    public function getContenuto(): string
    {
        return $this->contenuto;
    }

    /**
     * Restituisce la risposta corretta (true = corretta, false = errata)
     * @return bool
     */
    public function getRispostaCorretta(): bool
    {
        return $this->rispostaCorretta;
    }

    //---------------------- METODI SET -----------------------------

    /**
     * Imposta il testo della domanda
     * @param string $contenuto
     */
    public function setContenuto(string $contenuto): void
    {
        $this->contenuto = $contenuto;
    }

    /**
     * Imposta la risposta corretta (true/false)
     * @param bool $risposta
     */
    public function setRispostaCorretta(bool $risposta): void
    {
        $this->rispostaCorretta = $risposta;
    }

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
     //---------------------Implementazione per la serializzazione JSON-------------------------------
/**
     * Implementazione del metodo JsonSerializable
     * @return array
     */
    public function jsonSerialize(): array {
        return [
            'idDomanda' => $this->idDomanda,
            'contenuto' => $this->contenuto,
            'rispostaCorretta' => $this->rispostaCorretta
        ];
    }
}
