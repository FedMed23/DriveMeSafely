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
	private float $stipendio;
	
    //-------------------------COSTRUTTORE-------------------------
    
	public function __construct(
		string $_ruolo,
		ESpesa $_spesa
	) {
		//Costruttore genitore
		parent::__construct($_nome, $_cognome, $_username, $_email, $_password);
        
        //Inizializzazione attributi propri
		$this->ruolo = $_ruolo;
		$this->stipendio= $_spesa.getImporto();
	}

    //----------------------METODI GET-----------------------------
	
	public function getRuolo(): string {
		return $this->ruolo;
	}

	public function getStipendio(): float
    {
        return $this->stipendio;
    }

    //-----------------------------METODI SET-----------------------------

	public function setRuolo(string $_ruolo): void {
		$this->ruolo = $_ruolo;
	}

    public function setStipendio(float $_stipendio): void
    {
        $this->stipendio = $_stipendio;
    }
    //---------------------SERIALIZZAZIONE E STAMPA-------------------------------

	public function jsonSerialize(): array {
        // Unisce l'array del genitore con i nuovi attributi
		return parent::jsonSerialize() + [
			'ruolo' => $this->ruolo,
			'stipendio' => $this->stipendio,
		];
	}

	public function __toString(): string {
		$print = parent::__toString();
		$print .= "Ruolo: {$this->ruolo}\n";
		$print .= "Stipendio: €{$this->stipendio}\n";
		return $print;
	}
}
 ?>
