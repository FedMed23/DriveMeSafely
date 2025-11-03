<?php
/**
 * La classe EDipendente estende la classe EUtenteRegistrato e aggiunge
 * i dati specifici per il personale interno della Scuola Guida (es. il ruolo).
 */
class EDipendente extends EUtenteRegistrato {

	/**
	 * Ruolo del dipendente (e.g., Istruttore, Amministratore, Segretario).
	 * @var string
	 */
	private string $ruolo;
	
    //-------------------------COSTRUTTORE-------------------------
    
	public function __construct(
		string $_nome,
		string $_cognome,
		string $_username,
		string $_email,
		string $_password,
		string $_ruolo
	) {
		//Costruttore genitore
		parent::__construct($_nome, $_cognome, $_username, $_email, $_password);
        
        //Inizializzazione attributi propri
		$this->ruolo = $_ruolo;
	}

    //----------------------METODI GET-----------------------------
	
	public function getRuolo(): string {
		return $this->ruolo;
	}

    //-----------------------------METODI SET-----------------------------

	public function setRuolo(string $_ruolo): void {
		$this->ruolo = $_ruolo;
	}
    
    //---------------------SERIALIZZAZIONE E STAMPA-------------------------------

	public function jsonSerialize(): array {
        // Unisce l'array del genitore con i nuovi attributi
		return parent::jsonSerialize() + [
			'ruolo' => $this->ruolo
		];
	}

	public function __toString(): string {
		$print = parent::__toString();
		$print .= "Ruolo: {$this->ruolo}\n";
		return $print;
	}
}
 ?>
