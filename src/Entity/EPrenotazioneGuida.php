<?php
/**
 * La classe EPrenotazioneGuida rappresenta una prenotazione effettuata da un iscritto
 * per una determinata spesa, tramite una carta di credito.
 * 
 * Gli attributi che la descrivono sono:
 * - idPrenotazione: id della prenotazione
 * - idIscritto: oggetto della classe EIscritto
 * - idGuida: oggetto della classe EGuida
 * - data: data della prenotazione
 * - stato: stato della prenotazione (es. completato, in attesa, fallito)
 * 
 * @access public
 * @package Entity
 * @author Camasso-Medelago
 */

class EPrenotazioneGuida implements JsonSerializable
{   /**
     * id identificativo della prenotazione
     * @var int
     * */
     private ?int $idPr= null; 

    /**
     * Utente che effettua la prenotazione
     * @var EIscritto
     */
    private EIscritto $idIscritto;

    /**
     * Guida associata alla prenotazione
     * @var EGuida
     */
    private EGuida $idGuida;

    /**
     * Data della prenotazione
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $dataP;

    /**
     * Stato della prenotazione (es. completato, in attesa)
     * @var string
     */
    private string $stato;

    // ---------------- COSTRUTTORE ----------------

    /**
     * Crea una nuova istanza della classe EPrenotazioneGuida
     * 
     * @param EIscritto $idIscritto utente che effettua la prenotazione
     * @param EGuida $idGuida guida prenotata
     * @param DateTimeImmutable $data data della prenotazione
     * @param string $stato stato della prenotazione

     */
    public function __construct(
        EIscritto  $idIscritto ,
        EGuida $idGuida,
        DateTimeImmutable $data,
        string $stato
    ) {
        $this->idIscritto = $idIscritto;
        $this->idGuida = $idGuida;
        $this->dataP = $data;
        $this->stato = $stato;
    }
    //----------------------METODI GET/SET (ID)-----------------------------
    
    public function getId(): ?int { return $this->idPr; }
    public function setId(int $id): void { $this->idPr= $id; } 


    // ---------------- METODI GET ----------------

    public function getIdIscritto(): EIscritto
    {
        return $this->idIscritto;
    }

    public function getIdGuida(): EGuida
    {
        return $this->idGuida;
    }

    public function getData(): DateTimeImmutable
    {
        return $this->dataP;
    }

    public function getStato(): string
    {
        return $this->stato;
    }

    // ---------------- METODI SET ----------------

    public function setIscritto(EIscritto $idIscritto): void
    {
        $this->idIscritto= $idIscritto;
    }

    public function setIdGuida(EGuida $idGuida): void
    {
        $this->idGuida = $idGuida;
    }

    public function setData(DateTimeImmutable $data): void
    {
        $this->dataP = $data;
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
        $dataFormattata = $this->dataP->format('d-m-Y');
        return "idPrenotazioneGuida: {$this->getId()}\nIscritto: {$this->idIscritto}\n Guida: {$this->idGuida}\nData: {$dataFormattata}\nStato: {$this->stato}\n";
    }

    // --- Implementazione per la serializzazione JSON ---

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->idPr,
            'idIscritto' => $this->idIscritto,
            'idGuida' => $this->idGuida,
            'data' => $this->dataP->format('Y-m-d'),
            'stato' => $this->stato,
        ];
    }
}

?> 
