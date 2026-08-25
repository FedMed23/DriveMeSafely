<?php
namespace CamassoMedelago\DriveMeSafely\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 * La classe EProprietario estende EUtenteRegistrato.
 * E' l'utente con il massimo livello di accesso amministrativo,
 * Non ha attributi di dominio.
 * @ORM\Entity
 * @ORM\Table(name="proprietario")
 */
class EProprietario extends EUtenteRegistrato
{
    //------------------- COSTRUTTORI -------------------

    /**
     * Costruttore vuoto obbligatorio per le specifiche JPA/Hibernate.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setStatoAttivato();
    }

    /**
     * Costruttore completo della classe EProprietario.
     * Invoca il costruttore del genitore per inizializzare tutti gli attributi di base.
     *
     * @param string $nome Nome del proprietario
     * @param string $cognome Cognome del proprietario
     * @param string $username Username dell'account amministratore
     * @param string $email Email del proprietario
     * @param string $password Password dell'account amministratore
     * @param bool $stato Stato dell'account
     */
    public function init(
        string $nome,
        string $cognome,
        string $username,
        string $email,
        string $password,
        bool $stato
    ): void {
        parent::__construct(
            $nome,
            $cognome,
            $email,
            $username,
            $password,
            $stato
        );

        $this->setStatoAttivato();
    }


    //------------------- TOSTRING -------------------

    public function __toString(): string
    {
        return "Proprietario [" . parent::__toString() . "]";
    }
}

?>
