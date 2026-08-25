<?php

namespace CamassoMedelago\DriveMeSafely\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

/**
 * Entità ponte che registra l'atto di prenotazione di uno slot
 * del palinsesto lezioni da parte di un allievo iscritto.
 *
 * @access public
 * @package Entity
 * @author Camasso-Medelago
 *
 * @ORM\Entity
 * @ORM\Table(name="prenotazione_lezione")
 */
class EPrenotazioneLezione implements \JsonSerializable
{
    /**
     * Identificativo univoco della prenotazione.
     *
     * @var int|null
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id_prenotazione", type="integer")
     */
    private ?int $idPrenotazione = null;

    /**
     * Allievo che effettua la prenotazione.
     *
     * @var EIscritto
     * @ORM\ManyToOne(targetEntity="EIscritto")
     * @ORM\JoinColumn(
     *     name="id_iscritto",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     */
    private EIscritto $iscritto;

    /**
     * Lezione prenotata.
     *
     * La relazione è polimorfa: può essere una ELezioneTeoria
     * oppure una ELezionePratica.
     *
     * @var ELezione
     * @ORM\ManyToOne(targetEntity="ELezione")
     * @ORM\JoinColumn(
     *     name="id_lezione",
     *     referencedColumnName="id_lezione",
     *     nullable=false
     * )
     */
    private ELezione $lezione;

    /**
     * Data e ora della prenotazione.
     *
     * @var DateTimeImmutable
     * @ORM\Column(
     *     name="data_prenotazione",
     *     type="datetime_immutable",
     *     nullable=false
     * )
     */
    private DateTimeImmutable $dataPrenotazione;

    /**
     * Stato della prenotazione.
     *
     * Valore predefinito: PRENOTATA.
     *
     * @var string
     * @ORM\Column(
     *     name="stato",
     *     type="string",
     *     length=50,
     *     nullable=false
     * )
     */
    private string $stato = "PRENOTATA";

    /**
     * Presenza dell'allievo.
     *
     * true  = presente
     * false = assente
     * null  = non ancora registrato
     *
     * @var bool|null
     * @ORM\Column(
     *     name="presente",
     *     type="boolean",
     *     nullable=true
     * )
     */
    private ?bool $presente = null;

    /**
     * Note dell'istruttore.
     *
     * @var string|null
     * @ORM\Column(
     *     name="note_istruttore",
     *     type="string",
     *     length=500,
     *     nullable=true
     * )
     */
    private ?string $noteIstruttore = null;

    /**
     * Voto conseguito nella guida.
     *
     * @var int|null
     * @ORM\Column(
     *     name="voto_guida",
     *     type="integer",
     *     nullable=true
     * )
     */
    private ?int $votoGuida = null;


    // ---------------- COSTRUTTORI ----------------

    /**
     * Costruttore vuoto.
     *
     * Necessario a Doctrine.
     */
    public function __construct()
    {
        $this->dataPrenotazione = new DateTimeImmutable();
        $this->stato = "PRENOTATA";
        $this->presente = null;
    }

    /**
     * Costruttore completo.
     */
    public static function crea(
        EIscritto $iscritto,
        ELezione $lezione,
        ?string $stato = null
    ): self {
        $prenotazione = new self();

        $prenotazione->iscritto = $iscritto;
        $prenotazione->lezione = $lezione;

        $prenotazione->stato = $stato !== null
            ? strtoupper(trim($stato))
            : "PRENOTATA";

        $prenotazione->presente = null;

        return $prenotazione;
    }


    // ---------------- GETTER E SETTER ----------------

    public function getIdPrenotazione(): ?int
    {
        return $this->idPrenotazione;
    }

    public function setIdPrenotazione(int $id): void
    {
        $this->idPrenotazione = $id;
    }


    public function getIscritto(): EIscritto
    {
        return $this->iscritto;
    }

    public function setIscritto(EIscritto $iscritto): void
    {
        $this->iscritto = $iscritto;
    }


    public function getLezione(): ELezione
    {
        return $this->lezione;
    }

    public function setLezione(ELezione $lezione): void
    {
        $this->lezione = $lezione;
    }


    public function getDataPrenotazione(): DateTimeImmutable
    {
        return $this->dataPrenotazione;
    }

    public function setDataPrenotazione(
        DateTimeImmutable $data
    ): void {
        $this->dataPrenotazione = $data;
    }


    public function getStato(): string
    {
        return $this->stato;
    }

    public function setStato(?string $stato): void
    {
        $this->stato = $stato !== null
            ? strtoupper(trim($stato))
            : "PRENOTATA";
    }


    public function getPresente(): ?bool
    {
        return $this->presente;
    }

    public function setPresente(?bool $presente): void
    {
        $this->presente = $presente;
    }


    public function getNoteIstruttore(): ?string
    {
        return $this->noteIstruttore;
    }

    public function setNoteIstruttore(?string $note): void
    {
        $this->noteIstruttore = $note;
    }


    public function getVotoGuida(): ?int
    {
        return $this->votoGuida;
    }

    public function setVotoGuida(?int $voto): void
    {
        $this->votoGuida = $voto;
    }


    // ---------------- JSON ----------------

    public function jsonSerialize(): array
    {
        return [
            'idPrenotazione' => $this->idPrenotazione,

            'idIscritto' => $this->iscritto->getId(),

            'idLezione' => $this->lezione->getIdLezione(),

            'dataPrenotazione' =>
                $this->dataPrenotazione->format('Y-m-d H:i:s'),

            'stato' => $this->stato,

            'presente' => $this->presente,

            'noteIstruttore' => $this->noteIstruttore,

            'votoGuida' => $this->votoGuida
        ];
    }


    // ---------------- TOSTRING ----------------

    public function __toString(): string
    {
        $dataFormattata =
            $this->dataPrenotazione->format('d-m-Y H:i');

        return "idPrenotazione: " .
            ($this->idPrenotazione ?? 'N/D') .
            "\nIscritto: " .
            $this->iscritto->getId() .
            "\nLezione: " .
            $this->lezione->getIdLezione() .
            "\nData Prenotazione: " .
            $dataFormattata .
            "\nStato: " .
            $this->stato .
            "\nPresente: " .
            ($this->presente === null
                ? "Non registrato"
                : ($this->presente ? "Presente" : "Assente")) .
            "\nNote Istruttore: " .
            ($this->noteIstruttore ?? "Nessuna") .
            "\nVoto Guida: " .
            ($this->votoGuida ?? "N/D") .
            "\n";
    }
}
