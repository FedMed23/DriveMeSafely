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
public class Dipendente extends UtenteRegistrato implements Serializable {

    /**
     * Ruolo del dipendente.
     */
    @Column(name = "ruolo", length = 100, nullable = false)
    private String ruolo;

    /**
     * Stipendio del dipendente.
     */
    @Column(name = "stipendio", nullable = false)
    private float stipendio;


    // ------------------- COSTRUTTORI -------------------

    /**
     * Costruttore vuoto obbligatorio per JPA/Hibernate.
     */
    public Dipendente() {
        super();
    }

    /**
     * Costruttore della classe Dipendente.
     *
     * Lo stipendio viene ricavato dall'oggetto Spesa,
     * come avveniva nella versione PHP.
     */
    public Dipendente(
            String nome,
            String cognome,
            String username,
            String email,
            String password,
            String ruolo,
            Spesa spesa
    ) {
        super(nome, cognome, username, email, password);

        this.ruolo = ruolo;
        this.stipendio = spesa.getImporto();
    }


    // ------------------- METODI GET -------------------

    public String getRuolo() {
        return ruolo;
    }

    public float getStipendio() {
        return stipendio;
    }


    // ------------------- METODI SET -------------------

    public void setRuolo(String ruolo) {
        this.ruolo = ruolo;
    }

    public void setStipendio(float stipendio) {
        this.stipendio = stipendio;
    }


    // ------------------- TOSTRING -------------------

    @Override
    public String toString() {
        return super.toString() +
                "Ruolo: " + ruolo + "\n" +
                "Stipendio: €" + stipendio + "\n";
    }
}
