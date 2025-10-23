<?php


/**
 * La classe EMediaUtente è un'estensione della classe EMedia, associa i media all'utente loggato.
 * Contiene i seguenti attributi e metodi:
 * -emailute è la email dell'utente
 *  @author Gruppo 8
 *  @package Entity
 */
class EMediaUtente extends EMedia implements  JsonSerializable {

	/**
	 * nome dell'utente
	 * @AttributeType string
	 */
    private $emailute;

//------------------COSTRUTTORE----------------------------

    public function __construct($fname,$email){
        parent::__construct($fname);
        $this->emailute = $email;
    }

//------------------METODI GET----------------------------

	/**
	 * @return string email utente
	 */
    public function getEmailUte(){
        return $this-> emailute;
    }


//----------------------METODI SET-------------------

	/**
	 * @param $email string email utente
	 */
	public function setMailUte($email){
		$this->emailute = $email;
	}

    public function jsonSerialize ()
    {
        return
            [
                'id'   => $this->getId(),
                'filename' => $this->getFname(),
                'data'   => $this->getData(),
                'emailute'   => $this->getEmailUte(),
				'type'   => $this->getType()
            ];
    }

//-------------------------------METODO TOSTRING-------------
	/**
	 * @return  $stamp String
	 */
    public function __toString(){
        $st = " | EmailUte: ".$this->getEmailUte();
		$stamp = parent::__toString().$st;
        return $stamp;
    }


}

