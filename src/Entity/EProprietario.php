<?php
/**
 * La classe EProprietario estende EUtenteRegistrato.
 * E' l'utente con il massimo livello di accesso amministrativo,
 * Non ha attributi di dominio.
 */
class EProprietario extends EUtenteRegistrato {
    
    public function __construct(
        string $_nome,
        string $_cognome,
        string $_username,
        string $_email,
        string $_password
    ) {
        // Costruttore del genitore per inizializzare tutti gli attributi base
        parent::__construct($_nome, $_cognome, $_username, $_email, $_password);
    }

}
?>