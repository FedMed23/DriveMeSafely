<?php

namespace Entity;
use Doctrine\ORM\Mapping as ORM; 


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
 * @ORM\Entity
 * @ORM\Table(name="pagamento")
 */

class EPagamento implements JsonSerializable
{
    /**
     * id identificativo del pagamento (chiave primaria)
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
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
     * @ORM\Column(type="datetime_immutable")
     */ 
    private DateTimeImmutable $data;

    /**
     * Stato del pagamento (es. completato, in attesa)
     * @var string
     * @ORM\Column(type="string", length=100)  
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
     /**
     * Restituisce l'identificativo del pagamento
     * @return int|null
     */
    public function getId(): ?int { return $this->idPag; }
      /**
     * Imposta l'identificativo del pagamento
     * @param int $id
     */
    public function setId(int $id): void { $this->idPag= $id; } 
    // ---------------- METODI GET ----------------
     /**
     * Restituisce l'utente che ha effettuato il pagamento
     * @return EUtenteRegistrato
     */
    public function getIdUtenteRegistrato(): EUtenteRegistrato
    {
        return $this->idUtenteRegistrato;
    }
    
    /**
     * Restituisce la spesa associata al pagamento
     * @return ESpesa
     */
    public function getIdSpesa(): ESpesa
    {
        return $this->idSpesa;
    }
     /**
     * Restituisce la data del pagamento
     * @return DateTimeImmutable
     */
    public function getData(): DateTimeImmutable
    {
        return $this->data;
    }
      /**
     * Restituisce lo stato del pagamento
     * @return string
     */
    public function getStato(): string
    {
        return $this->stato;
    }
      /**
     * Restituisce la carta di credito usata per il pagamento
     * @return ECartaDiCredito
     */
    public function getCartaDiCredito(): ECartaDiCredito
    {
        return $this->cartaDiCredito;
    }

    // ---------------- METODI SET ----------------
      /**
     * Imposta l'utente che effettua il pagamento
     * @param EUtenteRegistrato $idUtenteRegistrato
     */
    public function setIdUtenteRegistrato(EUtenteRegistrato $idUtenteRegistrato): void
    {
        $this->idUtenteRegistrato = $idUtenteRegistrato;
    }
     /**
     * Imposta la spesa associata al pagamento
     * @param ESpesa $idSpesa
     */
    public function setIdSpesa(ESpesa $idSpesa): void
    {
        $this->idSpesa = $idSpesa;
    }
    /**
     * Imposta la data del pagamento
     * @param DateTimeImmutable $data
     */
    public function setData(DateTimeImmutable $data): void
    {
        $this->data = $data;
    }
      /**
     * Imposta lo stato del pagamento
     * @param string $stato
     */
    public function setStato(string $stato): void
    {
        $this->stato = $stato;
    }
      /**
     * Imposta la carta di credito utilizzata
     * @param ECartaDiCredito $cartaDiCredito
     */
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
      /**
     * Serializza l'oggetto EPagamento in formato JSON
     * @return array Array associativo con i dati del pagamento
     */
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
