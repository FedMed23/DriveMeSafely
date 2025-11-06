<?php
/**
 * La classe EIscritto estende la classe EUtenteRegistrato con i dati specifici dell'iscritto per la Scuola Guida.
 * Eredita id, nome, cognome, username, email, password e stato.
 */
* @access public
 * @author Camasso-Medelago
 * @package Entity
 */

class EUtenteIscritto extends EUtenteRegistrato {

	private string $codiceFiscale;
    private DateTimeImmutable  $dataNascita; 
	private string $luogoNascita;
	private string $indirizzo;
	private string $numeroTelefono;
	private string $tipoPatente;
    

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
		string $_telefono,
		EPatente $_patente
	) {
	
		parent::__construct($_nome, $_cognome, $_username, $_email, $_password);

		
		$this->codiceFiscale = $_cf;
		$this->dataNascita = $_dataNascita;
		$this->luogoNascita = $_luogoNascita;
		$this->indirizzo = $_indirizzo;
		$this->numeroTelefono = $_telefono;
		$this->tipoPatente= $_patente.getTipo();
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
    
    public function getTipoPatente(): EPatente
    {
        return $this->tipoPatente;
    }
    //-----------------------------METODI SET-----------------------------

	public function setIndirizzo(string $_indirizzo): void {
		$this->indirizzo = $_indirizzo;
	}

	public function setNumeroTelefono(string $_telefono): void {
		$this->numeroTelefono = $_telefono;
	}

    /**
     * Imposta il tipo della patente
     * @param string $tipo
     */
    public function setTipoPatente(EPatente $_patente): void
    {
        $this->tipoPatente = $_patente.getTipo();
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
			'tipoPatente' => $this->tipoPatente
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
		$print .= 'Tipo Patente' {$this->tipoPatente}\n";

		return $print;
	}
}
?>
