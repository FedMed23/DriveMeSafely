<?php

namespace CamassoMedelago\DriveMeSafely\Entity;
use Doctrine\ORM\Mapping as ORM; 
use DateTimeImmutable;
use DateTime;


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

class EPagamento implements \JsonSerializable
{
    /**
     * id identificativo del pagamento
     *
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id_pag", type="integer")
     */
    private ?int $idPag = null;

    /**
     * Utente che effettua il pagamento
     *
     * @ORM\ManyToOne(targetEntity="EUtenteRegistrato", fetch="LAZY")
     * @ORM\JoinColumn(name="utente_id", nullable=false)
     */
    private EUtenteRegistrato $utenteRegistrato;

    /**
     * Spesa associata al pagamento
     *
     * @ORM\ManyToOne(targetEntity="ESpesa", fetch="LAZY")
     * @ORM\JoinColumn(name="spesa_id", referencedColumnName="id_spesa", nullable=false)
     */
    private ESpesa $spesa;

    /**
     * Data del pagamento
     *
     * @var DateTime
     * @ORM\Column(name="data_pagamento", type="date", nullable=false)
     */
    private DateTime $data;

    /**
     * Stato del pagamento
     *
     * @var string
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private string $stato;

    /**
     * Carta di credito utilizzata per il pagamento
     *
     * @ORM\ManyToOne(
     *     targetEntity="ECartaDiCredito",
     *     fetch="LAZY",
     *     cascade={"persist", "merge"}
     * )
     * @ORM\JoinColumn(name="carta_id", nullable=false)
     */
    private ECartaDiCredito $cartaDiCredito;


    // ---------------- COSTRUTTORI ----------------

    /**
     * Costruttore vuoto obbligatorio per Doctrine.
     */
    public function __construct()
    {
    }

    /**
     * Crea una nuova istanza della classe EPagamento.
     */
    public function init(
        EUtenteRegistrato $utenteRegistrato,
        ESpesa $spesa,
        ECartaDiCredito $cartaDiCredito
    ): void {
        $this->utenteRegistrato = $utenteRegistrato;
        $this->spesa = $spesa;
        $this->data = new DateTime();
        $this->stato = "PAGATO";
        $this->cartaDiCredito = $cartaDiCredito;
    }


    // ---------------- METODI GET ----------------

    public function getId(): ?int
    {
        return $this->idPag;
    }

    public function getUtenteRegistrato(): EUtenteRegistrato
    {
        return $this->utenteRegistrato;
    }

    public function getSpesa(): ESpesa
    {
        return $this->spesa;
    }

    public function getData(): DateTime
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

    /**
     * Restituisce la data formattata.
     */
    public function getDataFormattata(): string
    {
        if (!isset($this->data)) {
            return "";
        }

        return $this->data->format('d/m/Y');
    }


    // ---------------- METODI SET ----------------

    public function setId(?int $idPag): void
    {
        $this->idPag = $idPag;
    }

    public function setUtenteRegistrato(EUtenteRegistrato $utenteRegistrato): void
    {
        $this->utenteRegistrato = $utenteRegistrato;
    }

    public function setSpesa(ESpesa $spesa): void
    {
        $this->spesa = $spesa;
    }

    public function setData(DateTime $data): void
    {
        $this->data = $data;
    }

    public function setCartaDiCredito(ECartaDiCredito $cartaDiCredito): void
    {
        $this->cartaDiCredito = $cartaDiCredito;
    }

    public function setStato(): void
    {
        $this->stato = "PAGATO";
    }


    // ------------------ TOSTRING ---------------------------

    public function __toString(): string
    {
        return "Pagamento{" .
            "idPagamento=" . $this->idPag .
            ", utente=" . ($this->utenteRegistrato !== null
                ? $this->utenteRegistrato->getUsername()
                : "null") .
            ", spesa=" . ($this->spesa !== null
                ? $this->spesa->getTipologia()
                : "null") .
            ", data=" . $this->getDataFormattata() .
            ", stato='" . $this->stato . '\'' .
            '}';
    }


    // ------------------ JSON ---------------------------

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->idPag,
            'utenteRegistrato' => $this->utenteRegistrato,
            'spesa' => $this->spesa,
            'data' => $this->data->format('Y-m-d'),
            'stato' => $this->stato,
            'cartaDiCredito' => $this->cartaDiCredito
        ];
    }
}

?>
