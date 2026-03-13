<?php
namespace Entity;
use Doctrine\ORM\Mapping as ORM;


/**
*La classe EEffettuazioneEsami rappresenta lo svolgimento dell'esame da parte dell'utente iscritto alla scuola guida.
 * @access public
 * @author Camasso-Medelago
 * @package Entity
 * @ORM\Entity
 * @ORM\Table(name="effettuazione_esami")
 */

class EEffettuazioneEsami implements \JsonSerializable {

    /**
     * Identificativo univoco dell’effettuazione esame (può essere null finché non salvato) (chiave primaria)
     * @var int|null
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private ?int $idEffEs = null;

    /**
     * Esame associato all’effettuazione
     * @var EEsame
     * @ORM\ManyToOne(targetEntity="EEsame")
     * @ORM\JoinColumn(name="id_esame", referencedColumnName="idEsame", nullable=false)
     */
    private EEsame $esame;

    /**
     * ID dell’iscritto che ha svolto l’esame
     * @var int
     * @ORM\Column(type="integer")  
     */
    private int $idIscritto;

    /**
     * Numero di tentativi effettuati
     * @var int
     * @ORM\Column(type="integer")  
     */
    private int $tentativi;

    /**
     * Esito dell’esame (true se superato, false altrimenti)
     * @var bool
     * @ORM\Column(type="boolean")  
     */
    private bool $superato;

    //-------------------------COSTRUTTORE-------------------------

    /**
     * Costruttore della classe EEffettuazioneEsami
     * 
     * @param EEsame $esame Esame svolto
     * @param EIscritto $_iscritto Oggetto dell’iscritto che ha sostenuto l’esame
     * @param int $_tentativi Numero di tentativi effettuati
     * @param bool $_superato Esito dell’esame (true/false)
     */
    public function __construct(EEsame $esame, EIscritto $_iscritto, int $_tentativi, bool $_superato)
    {
        $this->esame = $esame;
        $this->idIscritto = $_iscritto->getId();
        $this->tentativi = $_tentativi;
        $this->superato = $_superato;
    }
    
    //----------------------METODI GET/SET (ID)-----------------------------
    
     /**
     * Restituisce l'ID dell’effettuazione esame
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->idEffEs;
    }

    /**
     * Imposta l'ID dell’effettuazione esame
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->idEffEs = $id;
    }


    //----------------------METODI GET-----------------------------
    
    /**
     * Restituisce l’esame associato
     * @return EEsame
     */
    public function getEsame(): EEsame
    {
        return $this->esame;
    }

    /**
     * Restituisce l’ID dell’iscritto che ha sostenuto l’esame
     * @return int
     */
    public function getIdIscritto(): int
    {
        return $this->idIscritto;
    }

    /**
     * Restituisce il numero di tentativi effettuati
     * @return int
     */
    public function getTentativi(): int
    {
        return $this->tentativi;
    }

    /**
     * Restituisce true se l’esame è stato superato
     * @return bool
     */
    public function isSuperato(): bool
    {
        return $this->superato;
    }

    //---------------------- METODI SET -----------------------------

    /**
     * Imposta l’iscritto (salvando solo l’ID)
     * @param EIscritto $iscritto
     */
    public function setIscritto(EIscritto $iscritto): void
    {
        $this->idIscritto = $iscritto->getId();
    }

    /**
     * Imposta l’esame associato
     * @param EEsame $esame
     */
    public function setEsame(EEsame $esame): void
    {
        $this->esame = $esame;
    }

    /**
     * Imposta il numero di tentativi
     * @param int $tentativi
     */
    public function setTentativi(int $tentativi): void
    {
        $this->tentativi = $tentativi;
    }

    /**
     * Imposta l’esito dell’esame (true = superato, false = non superato)
     * @param bool $superato
     */
    public function setSuperato(bool $superato): void
    {
        $this->superato = $superato;
    }

//--------------------TOSTRING--------------

/**
 * Stampa i dettagli dello svolgimento del quiz.
 * @return string
 */
public function __toString(): string
    {
        return "idEffettuazioneEsame: {$this->getId()}\nIscritto: {$this->idIscritto}\n Esame: {$this->esame}\nTentativi: {$this->tentativi}\nSuperato: {$this->superato}\n";
    }
 //---------------------Implementazione per la serializzazione JSON-------------------------------
     /**
     * Implementazione del metodo JsonSerializable
     * @return array
     */
    public function jsonSerialize(): array {
        return [
            'idEffEs' => $this->idEffEs,
            'Esame' => $this->esame,
            'iscrittoId' => $this->idIscritto,
            'tentativi' => $this->tentativi,
            'superato' => $this->superato,
        ];
    }
}
?>
