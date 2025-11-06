<?php
/**
 * La classe EPrenotazioneGuida rappresenta una prenotazione effettuata da un iscritto
 * per una determinata guida.
 * 
 * Gli attributi che la descrivono sono:
 * - idPr: id della prenotazione della guida
 * - idIscritto: id dell'iscritto che effettua la guida
 * - idGuida: id della guida da effettuare
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
     * Iscritto che effettua la prenotazione
     * @var int
     */
    private int $idIscritto;

    /**
     * Guida associata alla prenotazione
     * @var int
     */
    private int $idGuida;

    /**
     * Data della prenotazione
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $dataPr;

    /**
     * Stato della prenotazione (es. completato, in attesa)
     * @var string
     */
    private string $stato;

    // ---------------- COSTRUTTORE ----------------

    /**
     * Crea una nuova istanza della classe EPrenotazioneGuida
     * @param EIscritto $iscritto iscritto che effettua la prenotazione della guida
     * @param EGuida $guida guida prenotata
     * @param DateTimeImmutable $data data della prenotazione
     * @param string $stato stato della prenotazione

     */
    public function __construct(
        EIscritto  $iscritto ,
        EGuida $guida,
        DateTimeImmutable $data,
        string $stato
    ) {
        $this->idIscritto = $iscritto->getId();
        $this->idGuida = $guida->getId();
        $this->dataPr = $data;
        $this->stato = $stato;
    }
    //----------------------METODI GET/SET (ID)-----------------------------
    
    public function getId(): ?int { return $this->idPr; }
    public function setId(int $id): void { $this->idPr= $id; } 


    // ---------------- METODI GET ----------------

    public function getIdIscritto(): int
    {
        return $this->idIscritto;
    }

    public function getIdGuida(): int
    {
        return $this->idGuida;
    }

    public function getData(): DateTimeImmutable
    {
        return $this->dataPr;
    }

    public function getStato(): string
    {
        return $this->stato;
    }

    // ---------------- METODI SET ----------------

    public function setIdIscritto(EIscritto $iscritto): void
    {
        $this->idIscritto= $iscritto->getId();
    }

    public function setIdGuida(EGuida $guida): void
    {
        $this->idGuida = $guida->getId();
    }

    public function setData(DateTimeImmutable $data): void
    {
        $this->dataPr = $data;
    }

    public function setStato(string $stato): void
    {
        $this->stato = $stato;
    }

    // ------------------ TOSTRING ---------------------------

    /**
     * Stampa i dettagli della prenotazione guida
     * @return string
     */
    public function __toString(): string
    {
        $dataFormattata = $this->dataPr->format('d-m-Y');
        return "idPrenotazioneGuida: {$this->idPr}\nIscritto: {$this->idIscritto}\n Guida: {$this->idGuida}\nData Prenotazione: {$dataFormattata}\nStato: {$this->stato}\n";
    }

    // --- Implementazione per la serializzazione JSON ---

    public function jsonSerialize(): array
    {
        return [
            'idPrenotazioneGuida' => $this->idPr,
            'idIscritto' => $this->idIscritto,
            'idGuida' => $this->idGuida,
            'dataPrenotazione' => $this->dataPr->format('Y-m-d'),
            'stato' => $this->stato,
        ];
    }
}

?> 

