<?php

namespace CamassoMedelago\DriveMeSafely\Entity;
use Doctrine\ORM\Mapping as ORM;


/**
 * La classe ETentativoRisposta rappresenta una singola risposta data dall'utente 
 * ad una domanda durante lo svolgimento di un quiz.
 * Gli attributi che la descrivolo sono:
 * -idTent: id tentativo risposta
 * -domanda: oggetto della classe domanda
 * -rispostaUtente: risposta dell'utente che può essere True o False
 * -esito: riporta se la risposta dell'utente alla domanda è giusta o sbagliata (True o False)
 *  @author Camasso-Medelago
 *  @package Entity
 * @ORM\Entity
 * @ORM\Table(name="tentativo_risposta")
 */
class ETentativoRisposta implements \JsonSerializable
{
    /**
     * Identificativo univoco del tentativo (chiave primaria)
     *
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id_tentativo", type="integer")
     */
    private ?int $idTentativo = null;

    /**
     * Domanda associata alla risposta
     *
     * @var EDomanda
     * @ORM\ManyToOne(targetEntity="EDomanda", fetch="EAGER")
     * @ORM\JoinColumn(name="id_domanda", referencedColumnName="id_domanda", nullable=false)
     */
    private EDomanda $domanda;

    /**
     * Svolgimento del quiz associato al tentativo
     *
     * @var ESvolgimentoQuiz
     * @ORM\ManyToOne(targetEntity="ESvolgimentoQuiz", fetch="LAZY")
     * @ORM\JoinColumn(name="id_svolgimento", referencedColumnName="id_svolgimento", nullable=false)
     */
    private ?ESvolgimentoQuiz $svolgimentoQuiz = null;

    /**
     * Risposta dell'utente (true o false)
     *
     * @var bool
     * @ORM\Column(name="risposta_utente", type="boolean", nullable=false)
     */
    private bool $rispostaUtente;

    /**
     * Esito della risposta (true se corretta, false se errata)
     *
     * @var bool
     * @ORM\Column(name="corretta", type="boolean", nullable=false)
     */
    private bool $corretta;


    //-------------------------COSTRUTTORI-------------------------

    public function __construct()
    {
    }

    public function init(
        EDomanda $domanda,
        ESvolgimentoQuiz $svolgimentoQuiz,
        bool $rispostaUtente,
        bool $corretta
    ): void {
        $this->domanda = $domanda;
        $this->svolgimentoQuiz = $svolgimentoQuiz;
        $this->rispostaUtente = $rispostaUtente;
        $this->corretta = $corretta;
    }


    //----------------------METODI GET-----------------------------

    public function getIdTentativo(): ?int
    {
        return $this->idTentativo;
    }

    public function getDomanda(): EDomanda
    {
        return $this->domanda;
    }

    public function getSvolgimentoQuiz(): ?ESvolgimentoQuiz
    {
        return $this->svolgimentoQuiz;
    }

    public function isRispostaUtente(): bool
    {
        return $this->rispostaUtente;
    }

    public function isCorretta(): bool
    {
        return $this->corretta;
    }


    //----------------------METODI SET-----------------------------

    public function setIdTentativo(?int $idTentativo): void
    {
        $this->idTentativo = $idTentativo;
    }

    public function setDomanda(EDomanda $domanda): void
    {
        $this->domanda = $domanda;
    }

    public function setSvolgimentoQuiz(?ESvolgimentoQuiz $svolgimentoQuiz): void
    {
        $this->svolgimentoQuiz = $svolgimentoQuiz;
    }

    public function setRispostaUtente(bool $rispostaUtente): void
    {
        $this->rispostaUtente = $rispostaUtente;
    }

    public function setCorretta(bool $corretta): void
    {
        $this->corretta = $corretta;
    }


    //---------------------JSON-------------------------------

    public function jsonSerialize(): array
    {
        return [
            'idTentativo' => $this->idTentativo,
            'domandaId' => $this->domanda->getId(),
            'rispostaUtente' => $this->rispostaUtente,
            'corretta' => $this->corretta
        ];
    }


    //--------------------METODO TOSTRING--------------

    /**
     * Stampa i dettagli del tentativo.
     *
     * @return string
     */
    public function __toString(): string
    {
        $contenutoDomanda = ($this->domanda !== null)
            ? $this->domanda->getContenuto()
            : "N/D";

        return "TentativoRisposta{" .
            "idTentativo=" . $this->idTentativo .
            ", domanda='" . $contenutoDomanda . "'" .
            ", rispostaUtente=" . ($this->rispostaUtente ? "true" : "false") .
            ", corretta=" . ($this->corretta ? "true" : "false") .
            '}';
    }
}

?>
