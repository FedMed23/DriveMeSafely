<?php
namespace CamassoMedelago\DriveMeSafely\Entity;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

/** 
*La classe ECartaDiCredito contiene le proprietà e gli attributi riguardanti una carta di credito
* Gli attributi che la descrivono sono:
 * - nomeTitolare: nome del titolare della carta di credito
 * - cognomeTitolare: cognome del titolare della carta di credito
 * - dataScadenza: data di scadenza della carta di credito
 * - numeroCarta: numero della carta di credito
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
     * @ORM\Column(name="numero_carta", type="string", length=16, unique=true)
     */
    private string $numeroCarta;

    /**
     * Nome del titolare della carta.
     */
    /**
     * @ORM\Column(name="nome_titolare", type="string", length=100)
     */
    private string $nomeTitolare;

    /**
     * Cognome del titolare della carta.
     */
    /**
     * @ORM\Column(name="cognome_titolare", type="string", length=100)
     */
    private string $cognomeTitolare;

    /**
     * Data di scadenza della carta.
     */
    /**
     * @ORM\Column(name="data_scadenza", type="date_immutable")
     */
    private DateTimeImmutable $dataScadenza;


    // ------------------- COSTRUTTORI -------------------

    /**
     * Costruttore vuoto obbligatorio per JPA/Hibernate.
     */
    public function __construct(
        string $nomeTitolare = '',
        string $cognomeTitolare = '',
        ?DateTimeImmutable $dataScadenza = null,
        string $numeroCarta = ''
    ) {
        $this->nomeTitolare = $nomeTitolare;
        $this->cognomeTitolare = $cognomeTitolare;
        $this->dataScadenza = $dataScadenza ?? new DateTimeImmutable();
        $this->numeroCarta = $numeroCarta;
    }

    /**
     * Costruttore della classe CartaDiCredito.
     */


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

    /**
     * Restituisce il numero della carta mascherato.
     *
     * Esempio:
     * XXXX-XXXX-XXXX-1234
     */
    public function getNumeroCartaMascherato(): string {
        if ($this->numeroCarta === '' || strlen($this->numeroCarta) < 4) {
            return "XXXX";
        }

        return 'XXXX-XXXX-XXXX-' . substr($this->numeroCarta, -4);
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
        $this->numeroCarta = $numeroCarta;
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
