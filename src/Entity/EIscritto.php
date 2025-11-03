<?php
/**
 * La classe EIscritto estende la classe EUtenteRegistrato con i dati specifici dell'iscritto per la Scuola Guida.
 * Eredita id, nome, cognome, username, email, password e stato.
 */

class EUtenteIscritto extends EUtenteRegistrato {

	private string $codiceFiscale;
    private DateTimeImmutable  $dataNascita; 
	private string $luogoNascita;
	private string $indirizzo;
	private string $numeroTelefono;
    

    //-------------------------COSTRUTTORE-------------------------
    
	public function __construct(
		string $_nome,
		string $_cognome,
		string $_username,
		string $_email,
		string $_password,
		string $_cf,
		DateTimeImmutable $_dataNascita,
		string $_luogoNascita,
		string $_indirizzo,
		string $_telefono
	) {
	
		parent::__construct($_nome, $_cognome, $_username, $_email, $_password);

		
		$this->codiceFiscale = $_cf;
		$this->dataNascita = $_dataNascita;
		$this->luogoNascita = $_luogoNascita;
		$this->indirizzo = $_indirizzo;
		$this->numeroTelefono = $_telefono;
	}

    //----------------------METODI GET-----------------------------
	
	public function getCodiceFiscale(): string {
		return $this->codiceFiscale;
	}

	public function getDataNascita(): \DateTimeImmutable {
		return $this->dataNascita;
	}

	public function getLuogoNascita(): string {
		return $this->luogoNascita;
	}

	public function getIndirizzo(): string {
		return $this->indirizzo;
	}

	public function getNumeroTelefono(): string {
		return $this->numeroTelefono;
	}

    //-----------------------------METODI SET-----------------------------

	public function setIndirizzo(string $_indirizzo): void {
		$this->indirizzo = $_indirizzo;
	}

	public function setNumeroTelefono(string $_telefono): void {
		$this->numeroTelefono = $_telefono;
	}
    
    //---------------------JSON-------------------------------

	public function jsonSerialize(): array {
        // Unisce l'array del genitore con i nuovi attributi

		return parent::jsonSerialize() + [
			'codiceFiscale' => $this->codiceFiscale,
			'dataNascita' => $this->dataNascita->format('d/m/Y'),
			'luogoNascita' => $this->luogoNascita,
			'indirizzo' => $this->indirizzo,
			'numeroTelefono' => $this->numeroTelefono,
		];
	}

	//--------------------METODO TOSTRING--------------

	public function __toString(): string {
		$print = parent::__toString();

		$print .= "CF: {$this->codiceFiscale}\n";
		$print .= "Data Nascita: {$this->dataNascita->format('d/m/Y')}\n";
		$print .= "Luogo Nascita: {$this->luogoNascita}\n";
		$print .= "Indirizzo: {$this->indirizzo}\n";
		$print .= "Telefono: {$this->numeroTelefono}\n";

		return $print;
	}
}
?>