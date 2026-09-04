<?php
namespace CamassoMedelago\DriveMeSafely\Entity;
use Doctrine\ORM\Mapping as ORM;


/**
*La classe EEffettuazioneEsami rappresenta lo svolgimento dell'esame da parte dell'utente iscritto alla scuola guida.
 * Ogni effettuazione è legata alla prenotazione dell'esame da cui deriva: iscritto ed esame
 * sono quindi ricavabili tramite la prenotazione, senza doverli duplicare.
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
     * @ORM\Column(name="id_eff_es", type="integer")
     */
    private ?int $idEffEs = null;

    /**
     * Prenotazione esame da cui deriva questa effettuazione
     * @var EPrenotazioneEsami
     * @ORM\ManyToOne(targetEntity="EPrenotazioneEsami", fetch="EAGER")
     * @ORM\JoinColumn(name="id_prenotazione_esame", referencedColumnName="id_prenotazione_esame", nullable=false)
     */
    private EPrenotazioneEsami $prenotazioneEsame;

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
     * @param EPrenotazioneEsami $prenotazioneEsame Prenotazione esame da cui deriva l'effettuazione
     * @param int $_tentativi Numero di tentativi effettuati
     * @param bool $_superato Esito dell’esame (true/false)
     */
    public function __construct(EPrenotazioneEsami $prenotazioneEsame, int $_tentativi, bool $_superato)
    {
        $this->prenotazioneEsame = $prenotazioneEsame;
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
     * Restituisce la prenotazione esame associata
     * @return EPrenotazioneEsami
     */
    public function getPrenotazioneEsame(): EPrenotazioneEsami
    {
        return $this->prenotazioneEsame;
    }

    /**
     * Restituisce l’esame associato (ricavato dalla prenotazione)
     * @return EEsame
     */
    public function getEsame(): EEsame
    {
        return $this->prenotazioneEsame->getEsame();
    }

    /**
     * Restituisce l’iscritto che ha sostenuto l’esame (ricavato dalla prenotazione)
     * @return EIscritto
     */
    public function getIscritto(): EIscritto
    {
        return $this->prenotazioneEsame->getAllievo();
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
     * Imposta la prenotazione esame associata
     * @param EPrenotazioneEsami $prenotazioneEsame
     */
    public function setPrenotazioneEsame(EPrenotazioneEsami $prenotazioneEsame): void
    {
        $this->prenotazioneEsame = $prenotazioneEsame;
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
        return "idEffettuazioneEsame: {$this->getId()}\nPrenotazione: {$this->prenotazioneEsame->getIdPrenotazioneEsame()}\nTentativi: {$this->tentativi}\nSuperato: {$this->superato}\n";
    }
 //---------------------Implementazione per la serializzazione JSON-------------------------------
     /**
     * Implementazione del metodo JsonSerializable
     * @return array
     */
    public function jsonSerialize(): array {
        return [
            'idEffEs' => $this->idEffEs,
            'idPrenotazioneEsame' => $this->prenotazioneEsame->getIdPrenotazioneEsame(),
            'tentativi' => $this->tentativi,
            'superato' => $this->superato,
        ];
    }
}
?>
