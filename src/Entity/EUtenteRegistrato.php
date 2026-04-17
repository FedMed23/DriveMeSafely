<?php
namespace CamassoMedelago\DriveMeSafely\Entity;
use Doctrine\ORM\Mapping as ORM;

//Nota: punto da vedere: path foto profilo 
/**
 * La classe EUtenteRegistrato contiene tutti gli attributi e metodi base riguardanti gli utenti registrati alla scuola guida.
 * Contiene i seguenti attributi (e i relativi metodi):
 * -id: è un identificativo relativo agli utenti registrati
 * -nomeUtente: nome dell'utente
 * -cognomeUtente: cognome dell'utente
 * -username: username dell'utente
 * -email: email dell'utente
 * -password: password dell'utente
 * -statoUtente: se l'account è attivo o meno
 *  @author Camasso-Medelago
 *  @package Entity
 * @ORM\Entity
 * @ORM\Table(name="utente_registrato")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="tipo_utente", type="string")
 * @ORM\DiscriminatorMap({
 *     "utente" = "CamassoMedelago\DriveMeSafely\Entity\EUtenteRegistrato",
 *     "iscritto" = "CamassoMedelago\DriveMeSafely\Entity\EIscritto",
 *     "dipendente" = "CamassoMedelago\DriveMeSafely\Entity\EDipendente",
 *     "proprietario" = "CamassoMedelago\DriveMeSafely\Entity\EProprietario"
 * })
 */
class EUtenteRegistrato implements \JsonSerializable {
	/**
	 * id identificativo dell'utente
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * */
	 private ?int $id= null; 
     

	/**
	 * nome dell'utente
	 * @var string
	 * @ORM\Column(type="string", length=100)
	 */
    private string $nomeUtente;

	/**
	 * cognome dell'utente
	 * @var string
	 * @ORM\Column(type="string", length=100)
	 */
    private string $cognomeUtente;

    /**
	 * email dell'utente
	 * @var string
	 * @ORM\Column(type="string", length=100, unique=true)
	 */
    private string $email;

    /**
     * username dell'utente
     * @var string
	 * @ORM\Column(type="string", length=100, unique=true)
     */
    private string $username;

    /**
	 * password dell'utente
	 * @var string
	 * @ORM\Column(type="string", length=255)
	 */
    private string $password;

    /**
	 * stato dell'utente
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
    private bool $statoUtente;

//-------------------------COSTRUTTORE-------------------------

    public function __construct(string $nome, string $cognome, string $username, string $email, string $password) {

        $this->nomeUtente=$nome;
        $this->cognomeUtente=$cognome;
        //Path alla foto di default $this->fotoProfilo=
        $this->username=$username;
        $this->email=$email;
        $this->password= password_hash($password, PASSWORD_DEFAULT); //La password viene criptata tramite questo algoritmo di hash
        $this->statoUtente= true;
    }
    //----------------------METODI GET/SET (ID)-----------------------------
    
    public function getId(): ?int { return $this->id; }
    public function setId(int $id): void { $this->id= $id; } 
//----------------------METODI GET-----------------------------
    /**
    * @return String nome dell'utente
    */
    public function getNomeUtente(): string {
       return $this->nomeUtente;
    }
    /**
    * @return String cognome dell'utente
    */
    public function getCognomeUtente(): string {
       return $this->cognomeUtente;
    }

    /**
    * @return String nome utente dell'utente
    */
    public function getUsername(): string {
       return $this->username;
   }
    /**
    * @return String email dell'utente
    */
    public function getEmail(): string {    
       return $this->email;
    }
    /**
    * @return String password dell'utente
    */
     public function getPasswordHash(): string {
        return $this->password;
    }
	/**
	 * @return Boolean visibilità dell'utente
	 */
    public function getStatoUtente(): bool{
        return $this->statoUtente;
    }

    //-----------------------------METODI SET-----------------------------
   /**
    * @param String username utente
    */
    public function setUsername(string $username): void {
    $this->username=$username;
    }

    /**
    * @param String $email email utente
    */
    public function setEmail(string $email): void {
    $this->email=$email;
    }

    /**
    * @param String $password password utente
    */
    public function setPassword(string $password): void {
    $this->password = password_hash($password, PASSWORD_DEFAULT);
}

    /**
    * Cambia lo stato in false (disattivo)
    */
    public function setStatoDisattivato(): void {
        $this->statoUtente=false;
    }

	/**
    * Cambia lo stato in true (attivo)
    */
    public function setStatoAttivato(): void {
        $this->statoUtente=true;
    }
//---------------------Altri metodi-----------------------
    public function verificaPassword(string $password): bool {
         return password_verify($password, $this->password);
}

//---------------------JSON-------------------------------
	public function jsonSerialize(): array
	{
		return
			[   'id' => $this->id,
			    'nomeUtente'   => $this->nomeUtente,
				'cognomeUtente' => $this->cognomeUtente,
                'username'   => $this->username,
				'email' => $this->email,
				'statoUtente' => $this->statoUtente
			];
	}

//--------------------METODO TOSTRING--------------
    /**
    * Stampa lo stato dell'utente
    * @return string
    */
    public function StatoToString (): string {
        $account = null;
        if ($this->statoUtente)
            $account = "attivo";
        else
            $account = "disattivo";
        return $account;
    }

    /**
     * Stampa i dettagli dell'utente
     * @return $print String
     */
    public function __toString(): string  {
        $print =" Nome: ".$this->nomeUtente."\n".
			    " Cognome: ".$this->cognomeUtente."\n".
			    " Username: ".$this->username."\n".
			    " Email: ".$this->email."\n".
			    " Stato: ".$this->StatoToString()."\n";

       return $print;
    }
}

?>
    
    
    
    

