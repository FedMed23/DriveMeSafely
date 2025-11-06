<?php

//Nota: punti da vedere: id e path foto profilo 
/**
 * La classe EUtenteRegistrato contiene tutti gli attributi e metodi base riguardanti gli utenti registrati alla scuolaguida.
 * Contiene i seguenti attributi (e i relativi metodi):
 * -id: è un identificativo autoincrement, relativo agli utenti registrati;
 * -nomeUtente: nome dell'utente;
 * -cognomeUtente: cognome dell'utente.
 * -fotoProfilo: foto profilo dell'utente
 * -email: email dell'utente
 * -password: password dell'utente
 * -stato: se l'account è attivo o meno
 *  @author Camasso-Medelago
 *  @package Entity
 */
class EUtenteRegistrato implements JsonSerializable {
	/**
	 * id identificativo dell'utente
	 * @var int
     * */
	 private ?int $id= null; 
     

	/**
	 * nome dell'utente
	 * @var string
	 */
    private string $nomeUtente;

	/**
	 * cognome dell'utente
	 * @var string
	 */
    private string $cognomeUtente;

	/**
	 * foto profilo dell'utente (URL)
	 * @var string
	 
    private string $fotoProfilo;
    */

    /**
	 * email dell'utente
	 * @var string
	 */
    private string $email;

    /**
     * username dell'utente
     * @var string
     */
    private string $username;

    /**
	 * password dell'utente
	 * @var string
	 */
    private string $password;

    /**
	 * stato dell'utente
	 * @var bool
	 */
    private bool $statoUtente;

//-------------------------COSTRUTTORE-------------------------

    public function __construct(string $_nome, string $_cognome, string $_username, string $_email, string $_password) {

        //$this->id=$_id;
        $this->nomeUtente=$_nome;
        $this->cognomeUtente=$_cognome;
        //Path alla foto di default $this->fotoProfilo=
        $this->username=$_username;
        $this->email=$_email;
        $this->password= password_hash($_password, PASSWORD_DEFAULT); //La password viene criptata tramite questo algoritmo di hash
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
    public function setUsername(string $_username): void {
    $this->username=$_username;
    }

    /**
    * @param String $email email utente
    */
    public function setEmail(string $_email): void {
    $this->email=$_email;
    }

    /**
    * @param String $password password utente
    */
    public function setPassword(string $_newpassword): void{
    	$newpassword = password_hash($_newpassword, PASSWORD_DEFAULT);
        $this->password=$newpassword;
    }

    /**
    * Cambia lo stato in true (attivo)
    */
    public function setStatoDisattivato(): void {
        $this->statoUtente=false;
    }
    
//---------------------JSON-------------------------------
	public function jsonSerialize(): array
	{
		return
			[
				'nomeUtente'   => $this->getNomeUtente(),
				'cognomeUtente' => $this->getCognomeUtente(),
                'username'   => $this->getUsername(),
				'email' => $this->getEmail(),
				'statoUtente' => $this->getStatoUtente()
			];
	}

//--------------------METODO TOSTRING--------------
    /**
    * Stampa lo stato dell'utente
    * @return string
    */
    public function StatoToString (): string {
        $account = null;
        if ($this->getStatoUtente())
            $account = "attivo";
        else
            $account = "disattivo";
        return $account;
    }
    /**
     * Potrebbe servire
    * Stampa tutti gli emelemnti di un array come un unica stringa
    * @return String
    
    protected function ArrayToString ($arr) {
        $str = null;
        if (is_array($arr))
           foreach ($arr as $val) {
              $str = $str."-".$val;
           }
        else 
            $str = $arr;
        return $str;
    }
    */

    /**
     * Stampa i dettagli dell'utente
     * @return $print String
     */
    public function __toString(): string  {
        $print =" Nome: ".$this->getNomeUtente()."\n"." Cognome: ".$this->getCognomeUtente()."\n"." Username: ".$this->getUsername()."\n"." Email: ".$this->getEmail()."\n"." Stato: ".$this->StatoToString()."\n";

       return $print;
    }
}

?>
    
    
    
    

