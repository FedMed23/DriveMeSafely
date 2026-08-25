<?php
namespace CamassoMedelago\DriveMeSafely\Entity;
use CamassoMedelago\DriveMeSafely\Entity\EQuiz;
use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Entity\ETentativoRisposta;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
*La classe ESvolgimentoQuiz rappresenta lo svolgimento del quiz da parte dell'utente iscritto 
*alla scuola guida.
*Gli attributi che la descrivono sono:
* -idSvolgimento: id dello svolgimento del quiz
* -quiz: oggetto della classe EQuiz
* -idIscritto: l'id dell'iscritto che ha effettuato il quiz
* -dataSvolgimento: data e ora dello svolgimento del quiz
* -errori: errori commessi nel quiz
* -tentativiRisposta: array delle risposte dell'utente alle domande del quiz
* -superato: riporta se il quiz è statu superato o meno (True/False)
* @access public
* @package Entity
* @author Camasso-Medelago
* @ORM\Entity
* @ORM\Table(name="svolgimento_quiz")
*/

class ESvolgimentoQuiz implements \JsonSerializable
{
    /**
     * id identificativo dello svolgimento del quiz
     *
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id_svolgimento", type="integer")
     */
    private ?int $idSvolgimento = null;

    /**
     * Quiz svolto
     *
     * @ORM\ManyToOne(targetEntity="EQuiz", fetch="LAZY")
     * @ORM\JoinColumn(name="id_quiz", nullable=false)
     */
    private EQuiz $quiz;

    /**
     * Iscritto che ha svolto il quiz
     *
     * @ORM\ManyToOne(targetEntity="EIscritto", fetch="LAZY")
     * @ORM\JoinColumn(name="id_iscritto", nullable=false)
     */
    private EIscritto $iscritto;

    /**
     * Data e ora dello svolgimento del quiz
     *
     * @var DateTimeImmutable
     * @ORM\Column(name="data_svolgimento", type="datetime_immutable", nullable=false)
     */
    private \DateTimeImmutable $dataSvolgimento;

    /**
     * Numero errori fatti nel quiz
     *
     * @var int
     * @ORM\Column(name="errori", type="integer", nullable=false)
     */
    private int $errori;

    /**
     * Esito finale del quiz
     *
     * @var bool
     * @ORM\Column(name="superato", type="boolean", nullable=false)
     */
    private bool $superato;

    /**
     * Array di tentativi di risposta
     *
     * @ORM\OneToMany(
     *     targetEntity="ETentativoRisposta",
     *     mappedBy="svolgimentoQuiz",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true,
     *     fetch="LAZY"
     * )
     *
     * @var ETentativoRisposta[]
     */
    private array $tentativi = [];


    //-------------------------COSTRUTTORI-------------------------

    public function __construct()
    {
    }

    public function init(
        EQuiz $quiz,
        EIscritto $iscritto,
        \DateTimeImmutable $dataSvolgimento,
        int $errori,
        bool $superato
    ): void {
        $this->quiz = $quiz;
        $this->iscritto = $iscritto;
        $this->dataSvolgimento = $dataSvolgimento;
        $this->errori = $errori;
        $this->superato = $superato;
    }


    //----------------------METODI GET-----------------------------

    public function getIdSvolgimento(): ?int
    {
        return $this->idSvolgimento;
    }

    public function getQuiz(): EQuiz
    {
        return $this->quiz;
    }

    public function getIscritto(): EIscritto
    {
        return $this->iscritto;
    }

    public function getDataSvolgimento(): \DateTimeImmutable
    {
        return $this->dataSvolgimento;
    }

    public function getErrori(): int
    {
        return $this->errori;
    }

    public function isSuperato(): bool
    {
        return $this->superato;
    }

    /**
     * @return ETentativoRisposta[]
     */
    public function getTentativi(): array
    {
        return $this->tentativi;
    }


    //----------------------METODI SET-----------------------------

    public function setIdSvolgimento(?int $idSvolgimento): void
    {
        $this->idSvolgimento = $idSvolgimento;
    }

    public function setQuiz(EQuiz $quiz): void
    {
        $this->quiz = $quiz;
    }

    public function setIscritto(EIscritto $iscritto): void
    {
        $this->iscritto = $iscritto;
    }

    public function setDataSvolgimento(\DateTimeImmutable $dataSvolgimento): void
    {
        $this->dataSvolgimento = $dataSvolgimento;
    }

    public function setErrori(int $errori): void
    {
        $this->errori = $errori;
    }

    public function setSuperato(bool $superato): void
    {
        $this->superato = $superato;
    }

    public function setTentativi(array $tentativi): void
    {
        $this->tentativi = $tentativi;
    }


    //----------------------METODI DI SUPPORTO-----------------------------

    public function addTentativo(ETentativoRisposta $tentativo): void
    {
        if ($tentativo !== null) {
            $this->tentativi[] = $tentativo;

            $tentativo->setSvolgimentoQuiz($this);

            // Logica di auto-computazione di sicurezza
            if (!$tentativo->isCorretta()) {
                $this->errori++;
            }

            // Aggiorna lo stato: promosso se gli errori complessivi sono inferiori o uguali a 3
            $this->superato = ($this->errori <= 3);
        }
    }

    public function removeTentativo(ETentativoRisposta $tentativo): void
    {
        if ($tentativo !== null) {
            $key = array_search($tentativo, $this->tentativi, true);

            if ($key !== false) {
                unset($this->tentativi[$key]);
                $this->tentativi = array_values($this->tentativi);
            }

            $tentativo->setSvolgimentoQuiz(null);

            // Storna l'errore se stiamo rimuovendo un tentativo errato
            if (!$tentativo->isCorretta() && $this->errori > 0) {
                $this->errori--;
            }

            $this->superato = ($this->errori <= 3);
        }
    }


    //---------------------JSON-------------------------------

    public function jsonSerialize(): array
    {
        return [
            'idSvolgimento' => $this->idSvolgimento,
            'quizId' => $this->quiz->getId(),
            'iscrittoId' => $this->iscritto->getId(),
            'dataSvolgimento' => $this->dataSvolgimento->format('Y-m-d H:i:s'),
            'errori' => $this->errori,
            'superato' => $this->superato,
            'tentativi' => $this->tentativi
        ];
    }


    //--------------------METODO TOSTRING--------------

    /**
     * Stampa i dettagli dello svolgimento del quiz.
     *
     * @return string
     */
    public function __toString(): string
    {
        return "SvolgimentoQuiz{" .
            "idSvolgimento=" . $this->idSvolgimento .
            ", quiz=" . ($this->quiz !== null ? $this->quiz->getNome() : "null") .
            ", iscritto=" . ($this->iscritto !== null ? $this->iscritto->getUsername() : "null") .
            ", dataSvolgimento=" . $this->dataSvolgimento->format('Y-m-d H:i:s') .
            ", errori=" . $this->errori .
            ", superato=" . ($this->superato ? "true" : "false") .
            '}';
    }
}

?>
