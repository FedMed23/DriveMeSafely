<?php

namespace CamassoMedelago\DriveMeSafely\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * La classe EQuiz rappresenta un set di domande.
 * Gli attributi che la definiscono sono:
 * -idQuiz: l'id del Quiz
 * -nomeQuiz: il nome del quiz
 * -domande: l'array di domande che compongono il quiz
 *
 * @access public
 * @package Entity
 * @author Camasso-Medelago
 * @ORM\Entity
 * @ORM\Table(name="quiz")
 */
class EQuiz implements \JsonSerializable
{
    /**
     * id identificativo del quiz
     *
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id_quiz", type="integer")
     */
    private ?int $idQuiz = null;

    /**
     * Nome del quiz
     *
     * @var string
     * @ORM\Column(name="nome", type="string", length=100, nullable=false)
     */
    private string $nome;

    /**
     * Descrizione del quiz
     *
     * @var string|null
     * @ORM\Column(name="descrizione", type="string", length=500, nullable=true)
     */
    private ?string $descrizione = null;

    /**
     * Numero di domande del quiz
     *
     * @var int
     * @ORM\Column(name="numero_domande", type="integer", nullable=false)
     */
    private int $numeroDomande;

    /**
     * Tempo massimo del quiz
     *
     * @var int
     * @ORM\Column(name="tempo_massimo", type="integer", nullable=false)
     */
    private int $tempoMassimo;

    /**
     * Insieme di domande del quiz
     *
     * @var EDomanda[]
     * @ORM\ManyToMany(targetEntity="EDomanda", fetch="LAZY")
     * @ORM\JoinTable(
     *     name="quiz_domanda",
     *     joinColumns={
     *         @ORM\JoinColumn(name="quiz_id", referencedColumnName="id_quiz")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="domanda_id", referencedColumnName="id_domanda")
     *     }
     * )
     * @ORM\OrderBy({"contenuto" = "ASC"})
     */
    private Collection $domande;


    //-------------------------COSTRUTTORI-------------------------

    public function __construct()
    {
        $this->domande = new ArrayCollection();
    }

    public function init(
        string $nome,
        ?string $descrizione,
        int $numeroDomande,
        int $tempoMassimo
    ): void {
        $this->nome = $nome;
        $this->descrizione = $descrizione;
        $this->numeroDomande = $numeroDomande;
        $this->tempoMassimo = $tempoMassimo;
    }


    //----------------------METODI GET/SET (ID)-----------------------------

    public function getIdQuiz(): ?int
    {
        return $this->idQuiz;
    }

    public function setIdQuiz(?int $idQuiz): void
    {
        $this->idQuiz = $idQuiz;
    }


    //----------------------METODI GET-----------------------------

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getDescrizione(): ?string
    {
        return $this->descrizione;
    }

    public function getNumeroDomande(): int
    {
        return $this->numeroDomande;
    }

    public function getTempoMassimo(): int
    {
        return $this->tempoMassimo;
    }

    /**
     * @return EDomanda[]
     */
    public function getDomande(): Collection
    {
        return $this->domande;
    }


    //-----------------------------METODI SET-----------------------------

    public function setNome(string $nome): void
    {
        $this->nome = $nome;
    }

    public function setDescrizione(?string $descrizione): void
    {
        $this->descrizione = $descrizione;
    }

    public function setNumeroDomande(int $numeroDomande): void
    {
        $this->numeroDomande = $numeroDomande;
    }

    public function setTempoMassimo(int $tempoMassimo): void
    {
        $this->tempoMassimo = $tempoMassimo;
    }

    public function setDomande(array $domande): void
    {
        $this->domande = new ArrayCollection($domande);
    }


    //----------------------Altri metodi-----------------------------

    /**
     * Aggiunge una domanda al quiz.
     */
    public function addDomanda(EDomanda $domanda): void
    {
        if (!$this->domande->contains($domanda)) {
            $this->domande->add($domanda);
        }
    }

    /**
     * Rimuove una domanda dal quiz.
     */
    public function removeDomanda(EDomanda $domanda): void
    {
        $this->domande->removeElement($domanda);
    }


    //---------------------JSON-------------------------------

    public function jsonSerialize(): array
    {
        return [
            'idQuiz' => $this->idQuiz,
            'nome' => $this->nome,
            'descrizione' => $this->descrizione,
            'numeroDomande' => $this->numeroDomande,
            'tempoMassimo' => $this->tempoMassimo,
            'domandeId' => array_map(
                fn($domanda) => $domanda->getIdDomanda(),
                $this->domande->toArray()
            )
        ];
    }


    //--------------------METODO TOSTRING--------------

    /**
     * Stampa i dettagli del quiz.
     *
     * @return string
     */
    public function __toString(): string
    {
        return "Quiz{" .
            "idQuiz=" . $this->idQuiz .
            ", nome='" . $this->nome . "'" .
            ", numeroDomande=" . $this->numeroDomande .
            ", tempoMassimo=" . $this->tempoMassimo . " minuti" .
            ", domandeCaricate=" . ($this->domande !== null ? count($this->domande) : 0) .
            '}';
    }
}

?>
