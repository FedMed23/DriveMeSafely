<?php

/**
 * La classe EPagamento rappresenta un pagamento effettuato da un utente registrato
 * per una determinata spesa, tramite una carta di credito.
 * 
 * Gli attributi che la descrivono sono:
 * - idUtenteRegistrato: oggetto della classe EUtenteRegistrato
 * - idSpesa: oggetto della classe ESpesa
 * - data: data del pagamento
 * - stato: stato del pagamento (es. completato, in attesa, fallito)
 * - cartaDiCredito: oggetto della classe ECartaDiCredito
 * 
 * @access public
 * @package Entity
 * @author Camasso-Medelago
 */

class EPagamento implements JsonSerializable
{
    /**
     * id identificativo del pagamento
     * @var int
     * */
     private ?int $idPag= null; 

    /**
     * Utente che effettua il pagamento
     * @var EUtenteRegistrato
     */
    private EUtenteRegistrato $idUtenteRegistrato;

    /**
     * Spesa associata al pagamento
     * @var ESpesa
     */
    private ESpesa $idSpesa;

    /**
     * Data del pagamento
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $data;

    /**
     * Stato del pagamento (es. completato, in attesa)
     * @var string
     */
    private string $stato;

    /**
     * Carta di credito utilizzata per il pagamento
     * @var ECartaDiCredito
     */
    private ECartaDiCredito $cartaDiCredito;

    // ---------------- COSTRUTTORE ----------------

    /**
     * Crea una nuova istanza della classe EPagamento
     * 
     * @param EUtenteRegistrato $idUtenteRegistrato utente che effettua il pagamento
     * @param ESpesa $idSpesa spesa pagata
     * @param DateTimeImmutable $data data del pagamento
     * @param string $stato stato del pagamento
     * @param ECartaDiCredito $cartaDiCredito carta usata per il pagamento
     */
    public function __construct(
        EUtenteRegistrato $idUtenteRegistrato,
        ESpesa $idSpesa,
        DateTimeImmutable $data,
        string $stato,
        ECartaDiCredito $cartaDiCredito
    ) {
        $this->idUtenteRegistrato = $idUtenteRegistrato;
        $this->idSpesa = $idSpesa;
        $this->data = $data;
        $this->stato = $stato;
        $this->cartaDiCredito = $cartaDiCredito;
    }
//----------------------METODI GET/SET (ID)-----------------------------
    
    public function getId(): ?int { return $this->idPag; }
    public function setId(int $id): void { $this->idPag= $id; } 
    // ---------------- METODI GET ----------------

    public function getIdUtenteRegistrato(): EUtenteRegistrato
    {
        return $this->idUtenteRegistrato;
    }

    public function getIdSpesa(): ESpesa
    {
        return $this->idSpesa;
    }

    public function getData(): DateTimeImmutable
    {
        return $this->data;
    }

    public function getStato(): string
    {
        return $this->stato;
    }

    public function getCartaDiCredito(): ECartaDiCredito
    {
        return $this->cartaDiCredito;
    }

    // ---------------- METODI SET ----------------

    public function setIdUtenteRegistrato(EUtenteRegistrato $idUtenteRegistrato): void
    {
        $this->idUtenteRegistrato = $idUtenteRegistrato;
    }

    public function setIdSpesa(ESpesa $idSpesa): void
    {
        $this->idSpesa = $idSpesa;
    }

    public function setData(DateTimeImmutable $data): void
    {
        $this->data = $data;
    }

    public function setStato(string $stato): void
    {
        $this->stato = $stato;
    }

    public function setCartaDiCredito(ECartaDiCredito $cartaDiCredito): void
    {
        $this->cartaDiCredito = $cartaDiCredito;
    }

    // ------------------ TOSTRING ---------------------------

    /**
     * Stampa i dettagli del pagamento
     * @return string
     */
    public function __toString(): string
    {
        $dataFormattata = $this->data->format('d-m-Y');
        return "idPagamento: {$this->getId()}\nUtente: {$this->idUtenteRegistrato}\nSpesa: {$this->idSpesa}\nData: {$dataFormattata}\nStato: {$this->stato}\nCarta di Credito: {$this->cartaDiCredito}\n";
    }

    // --- Implementazione per la serializzazione JSON ---

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->idPag,
            'idUtenteRegistrato' => $this->idUtenteRegistrato,
            'idSpesa' => $this->idSpesa,
            'data' => $this->data->format('Y-m-d'),
            'stato' => $this->stato,
            'cartaDiCredito' => $this->cartaDiCredito
        ];
    }
}

?> 
