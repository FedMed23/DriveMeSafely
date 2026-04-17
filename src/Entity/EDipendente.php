<?php
namespace CamassoMedelago\DriveMeSafely\Entity;
use Doctrine\ORM\Mapping as ORM;


/**
 * La classe EDipendente estende la classe EUtenteRegistrato e aggiunge
 * i dati specifici per il personale interno della Scuola Guida (es. il ruolo e lo stipendio).
 * @access public
 * @author Camasso-Medelago
 * @package Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="dipendente")
 */
class EDipendente extends EUtenteRegistrato {
     /**
     * Ruolo del dipendente
     * @var string
     * @ORM\Column(type="string", length=100)
     */ 
	private string $ruolo;
	/**
     * Stipendio del dipendente
     * @var float
     * @ORM\Column(type="float")
	 */
	private float $stipendio;
	
    //-------------------------COSTRUTTORE-------------------------
	/**
     * Costruttore della classe EDipendente.
     * 
     * @param string $_nome Nome del dipendente
     * @param string $_cognome Cognome del dipendente
     * @param string $_username Username dell’account utente
     * @param string $_email Email dell’utente registrato
     * @param string $_password Password dell’utente registrato
     * @param string $_ruolo Ruolo del dipendente (es. Istruttore)
     * @param ESpesa $_spesa Oggetto ESpesa da cui ricavare lo stipendio
     */
    public function __construct(
        string $_nome,
        string $_cognome,
        string $_username,
        string $_email,
        string $_password,
        string $_ruolo,
        ESpesa $_spesa
    ) {
		//Costruttore genitore
		parent::__construct($_nome, $_cognome, $_username, $_email, $_password);
        
        //Inizializzazione attributi propri
		$this->ruolo = $_ruolo;
		$this->stipendio= $_spesa->getImporto();
	}

    //----------------------METODI GET-----------------------------
	 /**
     * Restituisce il ruolo del dipendente
     * @return string
     */
	public function getRuolo(): string {
		return $this->ruolo;
	}
     /**
     * Restituisce lo stipendio del dipendente
     * @return float
     */
	public function getStipendio(): float
    {
        return $this->stipendio;
    }

    //-----------------------------METODI SET-----------------------------

	 /**
     * Imposta il ruolo del dipendente
     * @param string $_ruolo
     */
	public function setRuolo(string $_ruolo): void {
		$this->ruolo = $_ruolo;
	}
    /**
     * Imposta manualmente lo stipendio del dipendente
     * @param float $_stipendio
     */
    public function setStipendio(float $_stipendio): void
    {
        $this->stipendio = $_stipendio;
    }
   
// ---------------------------- TOSTRING ----------------------------
	 /**
     * Ritorna una rappresentazione testuale dell’oggetto EDipendente
     * @return string
     */
	public function __toString(): string {
		$print = parent::__toString();
		$print .= "Ruolo: {$this->ruolo}\n";
		$print .= "Stipendio: €{$this->stipendio}\n";
		return $print;
	}
	 //---------------------Implementazione per la serializzazione JSON-------------------------------
     /**
     * Implementazione del metodo JsonSerializable
     * @return array
     */
	public function jsonSerialize(): array {
        // Unisce l'array del genitore con i nuovi attributi
		return parent::jsonSerialize() + [
			'ruolo' => $this->ruolo,
			'stipendio' => $this->stipendio,
		];
	}
}
 ?>
