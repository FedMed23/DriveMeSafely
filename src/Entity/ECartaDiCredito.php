<?php
namespace CamassoMedelago\DriveMeSafely\Entity;

use CamassoMedelago\DriveMeSafely\Utils\CartaDiCreditoUtil;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

/** 
 * La classe ECartaDiCredito contiene le proprietà e gli attributi riguardanti una carta di credito
 * Gli attributi che la descrivono sono:
 * - nomeTitolare: nome del titolare della carta di credito
 * - cognomeTitolare: cognome del titolare della carta di credito
 * - dataScadenza: data di scadenza della carta di credito
 * - numeroCarta: hash crittografico del numero della carta di credito
 * - ultimeCifre: ultime 4 cifre della carta (per visualizzazione mascherata)
 *
 * @access public
 * @author Camasso-Medelago
 * @package Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="carta_di_credito")
 */
#[ORM\Entity]
#[ORM\Table(name: 'carta_di_credito')]
class ECartaDiCredito implements \JsonSerializable {

    /**
     * Identificativo della carta.
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer")
     */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: 'integer')]
    private ?int $id = null;

    /**
     * Hash SHA-256 del numero della carta.
     *
     * @ORM\Column(name="numero_carta", type="string", length=64, unique=true)
     */
    #[ORM\Column(name: 'numero_carta', type: 'string', length: 64, unique: true)]
    private string $numeroCarta;

    /**
     * Ultime 4 cifre della carta per visualizzazione e mascheramento sicuro.
     *
     * @ORM\Column(name="ultime_cifre", type="string", length=4, nullable=true)
     */
    #[ORM\Column(name: 'ultime_cifre', type: 'string', length: 4, nullable: true)]
    private ?string $ultimeCifre = null;

    /**
     * Nome del titolare della carta.
     *
     * @ORM\Column(name="nome_titolare", type="string", length=100)
     */
    #[ORM\Column(name: 'nome_titolare', type: 'string', length: 100)]
    private string $nomeTitolare;

    /**
     * Cognome del titolare della carta.
     *
     * @ORM\Column(name="cognome_titolare", type="string", length=100)
     */
    #[ORM\Column(name: 'cognome_titolare', type: 'string', length: 100)]
    private string $cognomeTitolare;

    /**
     * Data di scadenza della carta.
     *
     * @ORM\Column(name="data_scadenza", type="datetime_immutable")
     */
    #[ORM\Column(name: 'data_scadenza', type: 'datetime_immutable')]
    private DateTimeImmutable $dataScadenza;


    // ------------------- COSTRUTTORI -------------------

    /**
     * Costruttore della classe CartaDiCredito.
     */
    public function __construct(
        string $nomeTitolare = '',
        string $cognomeTitolare = '',
        ?DateTimeImmutable $dataScadenza = null,
        string $numeroCarta = '',
        ?string $ultimeCifre = null
    ) {
        $this->nomeTitolare = $nomeTitolare;
        $this->cognomeTitolare = $cognomeTitolare;
        $this->dataScadenza = $dataScadenza ?? new DateTimeImmutable();

        if ($numeroCarta !== '') {
            if (strlen($numeroCarta) === 64 && ctype_xdigit($numeroCarta)) {
                $this->numeroCarta = $numeroCarta;
                $this->ultimeCifre = $ultimeCifre;
            } else {
                $this->numeroCarta = CartaDiCreditoUtil::hashNumeroCarta($numeroCarta);
                $this->ultimeCifre = $ultimeCifre ?? CartaDiCreditoUtil::estraiUltimeCifre($numeroCarta);
            }
        } else {
            $this->numeroCarta = '';
            $this->ultimeCifre = $ultimeCifre;
        }
    }


    // ------------------- METODI GET -------------------

    public function getNomeTitolareCarta(): string {
        return $this->nomeTitolare;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getCognomeTitolareCarta(): string {
        return $this->cognomeTitolare;
    }

    public function getDataScadenza(): DateTimeImmutable {
        return $this->dataScadenza;
    }

    public function getNumeroCarta(): string {
        return $this->numeroCarta;
    }

    public function getUltimeCifre(): ?string {
        return $this->ultimeCifre;
    }

    /**
     * Restituisce il numero della carta mascherato (es. XXXX-XXXX-XXXX-1234).
     */
    public function getNumeroCartaMascherato(): string {
        if ($this->ultimeCifre !== null && strlen($this->ultimeCifre) === 4) {
            return 'XXXX-XXXX-XXXX-' . $this->ultimeCifre;
        }

        if ($this->numeroCarta !== '' && strlen($this->numeroCarta) >= 4 && strlen($this->numeroCarta) <= 19) {
            return CartaDiCreditoUtil::mascheraNumeroCarta($this->numeroCarta);
        }

        return 'XXXX-XXXX-XXXX-XXXX';
    }


    // ------------------- METODI SET -------------------

    public function setNomeTitolareCarta(string $nomeTitolare): void {
        $this->nomeTitolare = $nomeTitolare;
    }

    public function setCognomeTitolareCarta(string $cognomeTitolare): void {
        $this->cognomeTitolare = $cognomeTitolare;
    }

    public function setDataScadenza(DateTimeImmutable $dataScadenza): void {
        $this->dataScadenza = $dataScadenza;
    }

    public function setNumeroCarta(string $numeroCarta): void {
        if (strlen($numeroCarta) === 64 && ctype_xdigit($numeroCarta)) {
            $this->numeroCarta = $numeroCarta;
        } else {
            $this->numeroCarta = CartaDiCreditoUtil::hashNumeroCarta($numeroCarta);
            if ($this->ultimeCifre === null) {
                $this->ultimeCifre = CartaDiCreditoUtil::estraiUltimeCifre($numeroCarta);
            }
        }
    }

    public function setUltimeCifre(?string $ultimeCifre): void {
        $this->ultimeCifre = $ultimeCifre;
    }


    // ------------------- TOSTRING -------------------

    public function __toString(): string {
        return "Nome Titolare: {$this->nomeTitolare}\n"
            . "Cognome Titolare: {$this->cognomeTitolare}\n"
            . "Data Scadenza: " . $this->dataScadenza->format('m-Y') . "\n"
            . "Numero Carta: " . $this->getNumeroCartaMascherato() . "\n";
    }

    public function jsonSerialize(): array
    {
        return ['numeroCarta' => $this->getNumeroCartaMascherato()];
    }
}
