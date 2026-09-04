<?php
namespace CamassoMedelago\DriveMeSafely\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * La classe EDomanda rappresenta il contenuto di una domanda del quiz e la sua risposta corretta.
 
 * @access public
 * @author Camasso-Medelago
 * @package Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="domanda")
 */
#[ORM\Entity]
#[ORM\Table(name: 'domanda')]
class EDomanda implements \JsonSerializable
{
    /**
     * Identificativo univoco della domanda
     *
     * @var int|null
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id_domanda", type="integer")
     */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id_domanda', type: 'integer')]
    private ?int $idDomanda = null;

    /**
     * Testo della domanda
     *
     * @var string
     * @ORM\Column(type="string", length=300, nullable=false)
     */
    #[ORM\Column(name: 'contenuto', type: 'string', length: 300, nullable: false)]
    private string $contenuto;

    /**
     * Indica se la risposta è corretta
     *
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    #[ORM\Column(name: 'risposta_corretta', type: 'boolean', nullable: false)]
    private bool $rispostaCorretta;

    /**
     * Argomento della domanda
     *
     * @var string
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    #[ORM\Column(name: 'argomento', type: 'string', length: 100, nullable: false)]
    private string $argomento;

    /**
     * Quiz associati alla domanda
     *
     * @var EQuiz[]
     * @ORM\ManyToMany(targetEntity="EQuiz", mappedBy="domande")
     */
    #[ORM\ManyToMany(targetEntity: EQuiz::class, mappedBy: 'domande')]
    private Collection $quizAssociati;

    /**
     * Immagine associata alla domanda
     *
     * @var string|null
     * @ORM\Column(name="immagine", type="string", length=255, nullable=true)
     */
    #[ORM\Column(name: 'immagine', type: 'string', length: 255, nullable: true)]
    private ?string $immagine = null;


    // ------------------------- COSTRUTTORI -------------------------

    /**
     * Costruttore vuoto obbligatorio per Doctrine.
     */
    public function __construct()
    {
        $this->quizAssociati = new ArrayCollection();
    }

    /**
     * Costruttore completo della classe EDomanda.
     *
     * @param string $contenuto Testo della domanda
     * @param bool $rispostaCorretta Indica se la risposta è corretta
     * @param string $argomento Argomento della domanda
     * @param string|null $immagine Immagine associata alla domanda
     */
    public function init(
        string $contenuto,
        bool $rispostaCorretta,
        string $argomento,
        ?string $immagine
    ): void {
        $this->contenuto = $contenuto;
        $this->rispostaCorretta = $rispostaCorretta;
        $this->argomento = $argomento;
        $this->immagine = $immagine;
    }


    // ---------------------- METODI GET -----------------------------

    public function getIdDomanda(): ?int
    {
        return $this->idDomanda;
    }

    public function getContenuto(): string
    {
        return $this->contenuto;
    }

    public function isRispostaCorretta(): bool
    {
        return $this->rispostaCorretta;
    }

    public function getArgomento(): string
    {
        return $this->argomento;
    }

    /**
     * @return EQuiz[]
     */
    public function getQuiz(): Collection
    {
        return $this->quizAssociati;
    }

    public function getImmagine(): ?string
    {
        return $this->immagine;
    }


    // ---------------------- METODI SET -----------------------------

    public function setIdDomanda(?int $idDomanda): void
    {
        $this->idDomanda = $idDomanda;
    }

    public function setContenuto(string $contenuto): void
    {
        $this->contenuto = $contenuto;
    }

    public function setRispostaCorretta(bool $rispostaCorretta): void
    {
        $this->rispostaCorretta = $rispostaCorretta;
    }

    public function setArgomento(string $argomento): void
    {
        $this->argomento = $argomento;
    }

    /**
     * @param EQuiz[] $quiz
     */
    public function setQuiz(array $quiz): void
    {
        $this->quizAssociati = new ArrayCollection($quiz);
    }

    public function setImmagine(?string $immagine): void
    {
        $this->immagine = $immagine;
    }


    // -------------------- TOSTRING -------------------------------

    public function __toString(): string
    {
        return "Domanda{" .
            "id=" . $this->idDomanda .
            ", argomento='" . $this->argomento . '\'' .
            ", contenuto='" . $this->contenuto . '\'' .
            ", haImmagine=" . ($this->immagine !== null ? "Sì" : "No") .
            '}';
    }


    // --------------------- JSON -------------------------------

    public function jsonSerialize(): array
    {
        return [
            'idDomanda' => $this->idDomanda,
            'contenuto' => $this->contenuto,
            'rispostaCorretta' => $this->rispostaCorretta,
            'argomento' => $this->argomento,
            'quiz' => $this->quizAssociati->toArray(),
            'immagine' => $this->immagine
        ];
    }
}

?>
