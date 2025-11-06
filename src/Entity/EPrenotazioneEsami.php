<?php
/**
 * La classe EPrenotazioneEsami rappresenta una prenotazione effettuata da un dipendente
 * per un determinato esame.
 * 
 * Gli attributi che la descrivono sono:
 * - idPrenotazioneEsame: id della prenotazione dell'esame
 * - idDipendente: l'id del dipendente
 * - idEsame: l'id dell'esame
 * - data: data della prenotazione
 * - stato: stato della prenotazione (es. completato, in attesa, fallito)
 * 
 * @access public
 * @package Entity
 * @author Camasso-Medelago
 */

class EPrenotazioneEsami implements JsonSerializable
{   /**
     * id identificativo della prenotazione
     * @var int
     * */
     private ?int $idPrEs= null; 

    /**
     * Dipendente che effettua la prenotazione
     * @var int
     */
    private int $idDipendente;

    /**
     * Identificativo univoco dell'esame
     * @var int
     */
    private int $idEsame;

    /**
     * Data della prenotazione
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $dataPrEs;

    /**
     * Stato della prenotazione (es. completato, in attesa)
     * @var string
     */
    private string $stato;

    // ---------------- COSTRUTTORE ----------------

    /**
     * Crea una nuova istanza della classe EPrenotazioneEsami
     * @param EDipendente $dipendente dipendente che effettua la prenotazione
     * @param EEsame $esame esame prenotato
     * @param DateTimeImmutable $data data della prenotazione
     * @param string $stato stato della prenotazione

     */
    public function __construct(
        EDipendente $dipendente ,
        EEsame $esame,
        DateTimeImmutable $data,
        string $stato
    ) {
        $this->idDipendente = $dipendente->getId();
        $this->idEsame = $esame->getId();
        $this->dataPrEs = $data;
        $this->stato = $stato;
    }
    //----------------------METODI GET/SET (ID)-----------------------------
    
    public function getId(): ?int { return $this->idPrEs; }
    public function setId(int $id): void { $this->idPrEs= $id; } 


    // ---------------- METODI GET ----------------

    public function getIdDipendente(): int
    {
        return $this->idDipendente;
    }

    public function getIdEsame(): int
    {
        return $this->idEsame;
    }

    public function getData(): DateTimeImmutable
    {
        return $this->dataPrEs;
    }

    public function getStato(): string
    {
        return $this->stato;
    }

    // ---------------- METODI SET ----------------

    public function setIdDipendente(EDipendente $dipendente): void
    {
        $this->idDipendente= $dipendente->getId();
    }

    public function setIdEsame(EEsame $esame): void
    {
        $this->idEsame = $esame->getId();
    }

    public function setData(DateTimeImmutable $data): void
    {
        $this->dataPrEs = $data;
    }

    public function setStato(string $stato): void
    {
        $this->stato = $stato;
    }

    // ------------------ TOSTRING ---------------------------

    /**
     * Stampa i dettagli del pagamento
     * @return string
     */
    public function __toString(): string
    {
        $dataFormattata = $this->dataPrEs->format('d-m-Y');
        return "idPrenotazioneEsame: {$this->idPrEs}\nDipendente: {$this->idDipendente}\nID Esame: {$this->idEsame}\nData: {$dataFormattata}\nStato: {$this->stato}\n";
    }
 
    // ------- Implementazione per la serializzazione JSON --------

    public function jsonSerialize(): array
    {
        return [
            'idPrEs' => $this->idPrEs,
            'idDipendente' => $this->idDipendente,
            'idEsame' => $this->idEsame,
            'data' => $this->dataPrEs->format('Y-m-d'),
            'stato' => $this->stato,
        ];
    }
}

?> 
