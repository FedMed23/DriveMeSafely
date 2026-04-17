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
class EProprietario extends EUtenteRegistrato {
    
    public function __construct(
        string $nome,
        string $cognome,
        string $username,
        string $email,
        string $password
    ) {
        // Costruttore del genitore per inizializzare tutti gli attributi base
        parent::__construct($nome, $cognome, $username, $email, $password);
    }

}
?>
