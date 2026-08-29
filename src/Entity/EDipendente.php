<?php
namespace CamassoMedelago\DriveMeSafely\Entity;
use Doctrine\ORM\Mapping as ORM;


/**
 * La classe EDipendente estende la classe EUtenteRegistrato e aggiunge
 * i dati specifici per il personale interno della Scuola Guida (es. il ruolo e lo stipendio).
 * @access public
 * @author Camasso-Medelago
 * @package Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="dipendente")
 */
#[ORM\Entity]
#[ORM\Table(name: 'dipendente')]
class EDipendente extends EUtenteRegistrato
{

    /**
     * Ruolo del dipendente.
     * @ORM\Column(name="ruolo", type="string", length=100)
     */
    #[ORM\Column(name: 'ruolo', type: 'string', length: 100)]
    private string $ruolo;

    /**
     * Stipendio del dipendente.
     */
    private float $stipendio = 0.0;


    // ------------------- COSTRUTTORI -------------------

    /**
     * Costruttore vuoto obbligatorio per JPA/Hibernate.
     */
    public function __construct(
        string $nome = '',
        string $cognome = '',
        string $email = '',
        string $username = '',
        string $password = '',
        bool $stato = true,
        string $ruolo = '',
        float $stipendio = 0.0
    ) {
        parent::__construct($nome, $cognome, $email, $username, $password, $stato);
        $this->ruolo = $ruolo;
        $this->stipendio = $stipendio;
    }

    /**
     * Costruttore della classe Dipendente.
     *
     * Lo stipendio viene ricavato dall'oggetto Spesa,
     * come avveniva nella versione PHP.
     */


    // ------------------- METODI GET -------------------

    public function getRuolo(): string {
        return $this->ruolo;
    }

    public function getStipendio(): float {
        return $this->stipendio;
    }


    // ------------------- METODI SET -------------------

    public function setRuolo(string $ruolo): void {
        $this->ruolo = $ruolo;
    }

    public function setStipendio(float $stipendio): void {
        $this->stipendio = $stipendio;
    }


    // ------------------- TOSTRING -------------------

    public function __toString(): string {
        return parent::__toString() . "Ruolo: {$this->ruolo}\nStipendio: €{$this->stipendio}\n";
    }
}
