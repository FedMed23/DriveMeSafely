<?php

namespace CamassoMedelago\DriveMeSafely\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

/**
 * La classe ELezioneTeoria rappresenta una specifica lezione in aula.
 *
 * @access public
 * @package Entity
 * @author Camasso-Medelago
 *
 * @ORM\Entity
 */
class ELezioneTeoria extends ELezione
{
    /**
     * Aula nella quale si svolge la lezione.
     *
     * Nell'attuale versione PHP viene mantenuta come stringa,
     * in attesa della classe/enum Aula.
     *
     * @var string|null
     * @ORM\Column(name="aula", type="string", length=50, nullable=true)
     */
    private ?string $aula = null;

    /**
     * Argomento ministeriale della lezione.
     *
     * Nell'attuale versione PHP viene mantenuto come stringa,
     * in attesa dell'enum ArgomentoMinisteriale.
     *
     * @var string|null
     * @ORM\Column(name="argomento", type="string", length=50, nullable=true)
     */
    private ?string $argomentoLezione = null;

    // ---------------- COSTRUTTORE ----------------

    public function __construct(
        DateTimeImmutable $dataOra,
        ?string $aula = null,
        ?string $argomento = null
    ) {
        parent::__construct($dataOra);

        $this->aula = $aula;
        $this->argomentoLezione = $argomento;
    }

    // ---------------- GETTER ----------------

    public function getAula(): ?string
    {
        return $this->aula;
    }

    public function getArgomentoLezione(): ?string
    {
        return $this->argomentoLezione;
    }

    // ---------------- SETTER ----------------

    public function setAula(?string $aula): void
    {
        $this->aula = $aula;
    }

    public function setArgomentoLezione(?string $argomento): void
    {
        $this->argomentoLezione = $argomento;
    }

    // ---------------- JSON ----------------

    public function jsonSerialize(): array
    {
        return parent::jsonSerialize() + [
            'aula' => $this->aula,
            'argomentoLezione' => $this->argomentoLezione
        ];
    }

    // ---------------- TOSTRING ----------------

    public function __toString(): string
    {
        $dataFormattata = $this->getDataOra() !== null
            ? $this->getDataOra()->format('d-m-Y H:i')
            : 'N/D';

        return "LezioneTeoria{" .
            "id=" . ($this->getIdLezione() ?? 'N/D') .
            ", dataOra=" . $dataFormattata .
            ", argomento='" . $this->argomentoLezione . '\'' .
            ", aula='" . $this->aula . '\'' .
            '}';
    }
}
