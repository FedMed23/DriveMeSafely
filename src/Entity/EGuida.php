<?php

namespace Entity;
use Doctrine\ORM\Mapping as ORM;

/** 
*La classe EGuida contiene le proprietà e gli attributi riguardanti una guida in scuolaguida
* Gli attributi che la descrivono sono:
 * - numeroGuida: un numero che indica il numero della guida
 * - idDipendente: oggetto della classe EDipendente
 * - dataOra: data e ora in cui si svolge la guida
 * @access public
 * @author Camasso-Medelago
 * @package Entity
 * @ORM\Entity
 * @ORM\Table(name="guida")
 */

 class EGuida implements \JsonSerializable {
    /**
     * id identificativo dell'utente (chiave primaria)
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
     private ?int $idGuida= null; 

     #[ORM\ManyToOne(targetEntity: EDipendente::class)]
     #[ORM\JoinColumn(name: "id_dipendente", referencedColumnName: "id", nullable: false)]
     private EDipendente $idDipendente;

     /**
     * Numero della guida
     * @var int
     * @ORM\Column(type="integer")
     */  
     private int $numeroGuida;

     /**
     * Data e ora della guida 
     * @var DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */   
     private DateTimeImmutable $dataOra;

// -----------------------------COSTRUTTORE-----------------
     /** 
      * Crea una nuova istanza della classe EGuida
     * @param int $numeroGuida L'ID della guida.
     * @param EDipendente $idDipendente dipendente che effettua la guida.
     * @param \DateTimeImmutable $dataOra L'orario pianificato della guida
     */
     public function __construct(EDipendente $dipendente, int $numeroGuida, DateTimeImmutable $dataOra) {

        $this->idDipendente = $dipendente;
        $this->numeroGuida= $numeroGuida;
        $this->dataOra= $dataOra;
     }
    //----------------------METODI GET/SET (ID)-----------------------------
 /**
 * Restituisce l'identificativo univoco della guida.
 * 
 * @return int|null ID della guida se già assegnato, altrimenti null.
 */ 
   public function getId(): ?int
    {
        return $this->idGuida;
    }
    
 /**Imposta l'identificativo univoco della guida.
 * 
 * @param int $id Identificativo della guida da assegnare.
 */
    public function setId(int $id): void
    {
        $this->idGuida = $id;
    }

// ---------------------------- METODI GET ------------------------
     /**
     * @return int numero della guida
     */
    public function getNumeroGuida(): int {
        return $this->numeroGuida;
    }
    
    /**
     * Restituisce il dipendente associato alla guida
     * @return EDipendente
     */
    public function getIdDipendente(): EDipendente
    {
        return $this->idDipendente;
    }

    /**
     * @return \DateTimeImmutable Data e Ora della guida
     */
    public function getDataOra(): \DateTimeImmutable {
        return $this->dataOra;
    }
// ------------------ METODI SET ---------------------------
    /**
     * @param int $numeroGuida della guida
     */
    public function setNumeroGuida(int $numeroGuida): void  {
        $this->numeroGuida=$numeroGuida;
    }

   /**
     * Imposta il dipendente che tiene la guida
     * @param EDipendente $dipendente
     */
    public function setIdDipendente(EDipendente $dipendente): void
    {
        $this->idDipendente = $dipendente;
    }
    
    /**
     * @param \DateTimeImmutable $dataOra L'orario pianificato della guida
     */
    public function setDataOra(DateTimeImmutable $dataOra): void  {
        $this->dataOra=$dataOra;
    }

// ------------------ TOSTRING ---------------------------
    /**
     * Restituisce una rappresentazione testuale della guida
     * @return string
     */
    public function __toString(): string
    {
        $dataFormattata = $this->dataOra->format('d/m/Y H:i');
        return "Guida n° {$this->numeroGuida}\nData e ora: {$dataFormattata}\nDipendente: {$this->idDipendente}\n";
    }

// --- Implementazione per la serializzazione JSON ---
    /**
     * Serializza l'oggetto in formato JSON
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'numeroGuida' => $this->numeroGuida,
            'Dipendente' => $this->idDipendente,
            // Formatta l'oggetto DateTimeImmutable in una stringa leggibile
            'dataOra' => $this->dataOra->format('Y-m-d H:i:s')
        ]; 
    }
 }
 ?>
