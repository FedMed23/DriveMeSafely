<?php
namespace CamassoMedelago\DriveMeSafely\Entity;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use CamassoMedelago\DriveMeSafely\Entity\StatoPrenotazioneEsame;

/**
 * La classe EPrenotazioneEsami rappresenta una prenotazione effettuata da un dipendente
 * per un determinato esame.
 * 
 * Gli attributi che la descrivono sono:
 * - idPrenotazioneEsame: id della prenotazione dell'esame
 * - idDipendente: l'id del dipendente
 * - idEsame: l'id dell'esame
 * - data: data della prenotazione
 * - stato: stato della prenotazione (es. completato, in attesa, fallito)
 * 
 * @access public
 * @package Entity
 * @author Camasso-Medelago
 * @ORM\Entity
 * @ORM\Table(name="prenotazione_esami")
 */

class EPrenotazioneEsami implements \JsonSerializable
{
    /**
     * id identificativo della prenotazione
     *
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id_prenotazione_esame", type="integer")
     */
    private ?int $idPrenotazioneEsame = null;

    /**
     * Dipendente che ha inserito la pratica in Motorizzazione
     *
     * @ORM\ManyToOne(targetEntity="EDipendente", fetch="EAGER")
     * @ORM\JoinColumn(name="id_dipendente", nullable=false)
     */
    private EDipendente $dipendente;

    /**
     * Sessione d'esame a cui partecipa
     *
     * @ORM\ManyToOne(targetEntity="EEsame", fetch="EAGER")
     * @ORM\JoinColumn(name="id_esame", referencedColumnName="id_esame", nullable=false)
     */
    private EEsame $esame;

    /**
     * Iscritto che sostiene l'esame
     *
     * @ORM\ManyToOne(targetEntity="EIscritto", fetch="EAGER")
     * @ORM\JoinColumn(name="id_iscritto", nullable=false)
     */
    private EIscritto $allievo;

    /**
     * Data e ora della prenotazione
     *
     * @var DateTimeImmutable
     * @ORM\Column(name="data_prenotazione", type="datetime_immutable", nullable=false)
     */
    private \DateTimeImmutable $dataPrenotazione;

    /**
     * Stato della prenotazione
     *
     * @var StatoPrenotazioneEsame
     * @ORM\Column(name="stato", type="string", length=30, nullable=false, enumType="CamassoMedelago\DriveMeSafely\Entity\StatoPrenotazioneEsame")
     */
    private StatoPrenotazioneEsame $stato = StatoPrenotazioneEsame::PRENOTATO;


    //-------------------------COSTRUTTORI-------------------------

    /**
     * Costruttore vuoto obbligatorio per Doctrine.
     */
    public function __construct()
    {
        $this->dataPrenotazione = new \DateTimeImmutable();
        $this->stato = StatoPrenotazioneEsame::PRENOTATO;
    }

    /**
     * Costruttore completo della prenotazione.
     */
    public function init(
        EDipendente $dipendente,
        EEsame $esame,
        EIscritto $allievo,
        StatoPrenotazioneEsame|string $stato = StatoPrenotazioneEsame::PRENOTATO
    ): void {
        $this->dipendente = $dipendente;
        $this->esame = $esame;
        $this->allievo = $allievo;
        $this->dataPrenotazione = new \DateTimeImmutable();
        $this->stato = is_string($stato) ? (StatoPrenotazioneEsame::tryFrom($stato) ?? StatoPrenotazioneEsame::PRENOTATO) : $stato;
    }


    //----------------------METODI GETTER E SETTER-----------------------------

    public function getIdPrenotazioneEsame(): ?int
    {
        return $this->idPrenotazioneEsame;
    }

    public function setIdPrenotazioneEsame(?int $id): void
    {
        $this->idPrenotazioneEsame = $id;
    }

    public function getDipendente(): EDipendente
    {
        return $this->dipendente;
    }

    public function setDipendente(EDipendente $dipendente): void
    {
        $this->dipendente = $dipendente;
    }

    public function getEsame(): EEsame
    {
        return $this->esame;
    }

    public function setEsame(EEsame $esame): void
    {
        $this->esame = $esame;
    }

    public function getAllievo(): EIscritto
    {
        return $this->allievo;
    }

    public function setAllievo(EIscritto $allievo): void
    {
        $this->allievo = $allievo;
    }

    public function getDataPrenotazione(): \DateTimeImmutable
    {
        return $this->dataPrenotazione;
    }

    public function setDataPrenotazione(\DateTimeImmutable $data): void
    {
        $this->dataPrenotazione = $data;
    }

    public function getStato(): StatoPrenotazioneEsame
    {
        return $this->stato;
    }

    public function setStato(StatoPrenotazioneEsame|string $stato): void
    {
        $this->stato = is_string($stato) ? (StatoPrenotazioneEsame::tryFrom($stato) ?? StatoPrenotazioneEsame::PRENOTATO) : $stato;
    }


    //---------------------JSON-------------------------------

    public function jsonSerialize(): array
    {
        return [
            'idPrenotazioneEsame' => $this->idPrenotazioneEsame,
            'dipendenteId' => $this->dipendente->getId(),
            'esameId' => $this->esame->getIdEsame(),
            'allievoId' => $this->allievo->getId(),
            'dataPrenotazione' => $this->dataPrenotazione->format('Y-m-d H:i:s'),
            'stato' => $this->stato->value
        ];
    }


    //--------------------METODO TOSTRING--------------

    public function __toString(): string
    {
        return "Esame: " .
            $this->esame->getTipologia()->getDescrizione() .
            " | Allievo: " .
            $this->allievo->getCognome() .
            " | Stato: " .
            $this->stato->getDescrizione();
    }
}

?>
