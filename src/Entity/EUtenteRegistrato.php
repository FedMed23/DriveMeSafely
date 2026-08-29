<?php
namespace CamassoMedelago\DriveMeSafely\Entity;
use Doctrine\ORM\Mapping as ORM;
use CamassoMedelago\DriveMeSafely\Utils\PasswordUtil;

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
 *     "Iscritto" = "CamassoMedelago\DriveMeSafely\Entity\EIscritto",
 *     "Dipendente" = "CamassoMedelago\DriveMeSafely\Entity\EDipendente",
 *     "Proprietario" = "CamassoMedelago\DriveMeSafely\Entity\EProprietario"
 * })
 */
abstract class EUtenteRegistrato implements \JsonSerializable
{
    /**
     * id identificativo dell'utente
     *
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * nome dell'utente
     *
     * @var string
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private string $nome;

    /**
     * cognome dell'utente
     *
     * @var string
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private string $cognome;

    /**
     * username dell'utente
     *
     * @var string
     * @ORM\Column(type="string", length=100, unique=true, nullable=false)
     */
    private string $username;

    /**
     * email dell'utente
     *
     * @var string
     * @ORM\Column(type="string", length=100, unique=true, nullable=false)
     */
    private string $email;

    /**
     * password dell'utente
     *
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $password;

    /**
     * stato dell'utente
     *
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $stato;

    //-------------------------COSTRUTTORE-------------------------

    public function __construct(
        string $nome,
        string $cognome,
        string $email,
        string $username,
        string $password,
        bool $stato
    ) {
        $this->nome = $nome;
        $this->cognome = $cognome;
        $this->username = $username;
        $this->email = $email;
        $this->password = PasswordUtil::hashPassword($password);
        $this->stato = true;
        $this->stato = $stato;
    }

    //----------------------METODI GET/SET (ID)-----------------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    //----------------------METODI GET-----------------------------

    /**
     * @return string nome dell'utente
     */
    public function getNome(): string
    {
        return $this->nome;
    }

    /**
     * @return string cognome dell'utente
     */
    public function getCognome(): string
    {
        return $this->cognome;
    }

    /**
     * @return string username dell'utente
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string email dell'utente
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string password dell'utente
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return bool stato dell'utente
     */
    public function isStatoUtente(): bool
    {
        return $this->stato;
    }

    //-----------------------------METODI SET-----------------------------

    /**
     * @param string $nome nome utente
     */
    public function setNome(string $nome): void
    {
        $this->nome = $nome;
    }

    /**
     * @param string $cognome cognome utente
     */
    public function setCognome(string $cognome): void
    {
        $this->cognome = $cognome;
    }

    /**
     * @param string $username username utente
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @param string $email email utente
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @param string $password password utente
     */
    public function setPassword(string $password): void
    {
        $this->password = PasswordUtil::hashPassword($password);
    }

    /**
     * Cambia lo stato in true (attivo)
     */
    public function setStatoAttivato(): void
    {
        $this->stato = true;
    }

    /**
     * Cambia lo stato in false (disattivo)
     */
    public function setStatoDisattivato(): void
    {
        $this->stato = false;
    }

    //---------------------Altri metodi-----------------------

    public function verificaPassword(string $password): bool
    {
        return PasswordUtil::verifyPassword($password, $this->password);
    }

    //---------------------JSON-------------------------------

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'cognome' => $this->cognome,
            'username' => $this->username,
            'email' => $this->email,
            'stato' => $this->stato
        ];
    }

    //--------------------METODO TOSTRING--------------

    /**
     * Stampa lo stato dell'utente
     *
     * @return string
     */
    public function StatoToString(): string
    {
        $account = null;

        if ($this->stato) {
            $account = "attivo";
        } else {
            $account = "disattivo";
        }

        return $account;
    }

    /**
     * Stampa i dettagli dell'utente
     *
     * @return string
     */
    public function __toString(): string
    {
        $print = "UtenteRegistrato{" .
            "id=" . $this->id .
            ", nome='" . $this->nome . "'" .
            ", cognome='" . $this->cognome . "'" .
            ", username='" . $this->username . "'" .
            ", email='" . $this->email . "'" .
            ", statoUtente=" . ($this->stato ? "true" : "false") .
            "}";

        return $print;
    }
}

?>
