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
public class CartaDiCredito implements Serializable {

    /**
     * Numero della carta, utilizzato come chiave primaria.
     */
    @Id
    @Column(name = "numero_carta", length = 16, nullable = false)
    private String numeroCarta;

    /**
     * Nome del titolare della carta.
     */
    @Column(name = "nome_titolare", length = 100, nullable = false)
    private String nomeTitolare;

    /**
     * Cognome del titolare della carta.
     */
    @Column(name = "cognome_titolare", length = 100, nullable = false)
    private String cognomeTitolare;

    /**
     * Data di scadenza della carta.
     */
    @Column(name = "data_scadenza", nullable = false)
    private LocalDate dataScadenza;


    // ------------------- COSTRUTTORI -------------------

    /**
     * Costruttore vuoto obbligatorio per JPA/Hibernate.
     */
    public CartaDiCredito() {
    }

    /**
     * Costruttore della classe CartaDiCredito.
     */
    public CartaDiCredito(
            String nomeTitolare,
            String cognomeTitolare,
            LocalDate dataScadenza,
            String numeroCarta
    ) {
        this.nomeTitolare = nomeTitolare;
        this.cognomeTitolare = cognomeTitolare;
        this.dataScadenza = dataScadenza;
        this.numeroCarta = numeroCarta;
    }


    // ------------------- METODI GET -------------------

    public String getNomeTitolareCarta() {
        return this.nomeTitolare;
    }

    public String getCognomeTitolareCarta() {
        return this.cognomeTitolare;
    }

    public LocalDate getDataScadenza() {
        return this.dataScadenza;
    }

    /**
     * Restituisce il numero della carta mascherato.
     *
     * Esempio:
     * XXXX-XXXX-XXXX-1234
     */
    public String getNumeroCartaMascherato() {
        if (this.numeroCarta == null || this.numeroCarta.length() < 4) {
            return "XXXX";
        }

        return "XXXX-XXXX-XXXX-" +
                this.numeroCarta.substring(this.numeroCarta.length() - 4);
    }


    // ------------------- METODI SET -------------------

    public void setNomeTitolareCarta(String nomeTitolare) {
        this.nomeTitolare = nomeTitolare;
    }

    public void setCognomeTitolareCarta(String cognomeTitolare) {
        this.cognomeTitolare = cognomeTitolare;
    }

    public void setDataScadenza(LocalDate dataScadenza) {
        this.dataScadenza = dataScadenza;
    }

    public void setNumeroCarta(String numeroCarta) {
        this.numeroCarta = numeroCarta;
    }


    // ------------------- TOSTRING -------------------

    @Override
    public String toString() {

        String dataFormattata = "";

        if (this.dataScadenza != null) {
            DateTimeFormatter formatter =
                    DateTimeFormatter.ofPattern("MM-yyyy");

            dataFormattata = this.dataScadenza.format(formatter);
        }

        return "Nome Titolare: " + this.nomeTitolare + "\n" +
               " Cognome Titolare:" + this.cognomeTitolare + "\n" +
               " Data Scadenza: " + dataFormattata + "\n" +
               " Numero Carta: " + getNumeroCartaMascherato() + "\n";
    }
}
