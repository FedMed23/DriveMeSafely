<?php

namespace CamassoMedelago\DriveMeSafely\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

/**
 * La classe ELezioneTeoria rappresenta una specifica lezione in aula.
 *
 * Nota: il valore del discriminatore ("TEORIA") è già definito dalla mappa
 * dei discriminatori sulla classe madre ELezione (attributo DiscriminatorMap);
 * l'equivalente annotazione DiscriminatorValue in stile docblock non esiste
 * più in questa versione di Doctrine (sostituita dall'attributo PHP nativo),
 * ma non è comunque necessaria qui perché la mappa è già completa.
 *
 * @ORM\Entity
 */
#[ORM\Entity]
#[ORM\DiscriminatorValue('TEORIA')]
class ELezioneTeoria extends ELezione
{
    /**
     * Aula nella quale si svolge la lezione.
     *
     * @var EAula|null
     * @ORM\Column(name="aula", type="string", length=50, nullable=true, enumType="CamassoMedelago\DriveMeSafely\Entity\EAula")
     */
    #[ORM\Column(
        name: 'aula',
        type: 'string',
        length: 50,
        nullable: true,
        enumType: EAula::class
    )]
    private ?EAula $aula = null;

    /**
     * Argomento ministeriale della lezione.
     *
     * @var EArgomentoMinisteriale|null
     * @ORM\Column(name="argomento", type="string", length=50, nullable=true, enumType="CamassoMedelago\DriveMeSafely\Entity\EArgomentoMinisteriale")
     */
    #[ORM\Column(
        name: 'argomento',
        type: 'string',
        length: 50,
        nullable: true,
        enumType: EArgomentoMinisteriale::class
    )]
    private ?EArgomentoMinisteriale $argomentoLezione = null;

    // ---------------- COSTRUTTORE ----------------

    public function __construct(
        DateTimeImmutable $dataOra,
        ?EAula $aula = null,
        ?EArgomentoMinisteriale $argomento = null
    ) {
        parent::__construct($dataOra);

        $this->aula = $aula;
        $this->argomentoLezione = $argomento;
    }

    // ---------------- GETTER ----------------

    public function getAula(): ?EAula
    {
        return $this->aula;
    }

    public function getArgomentoLezione(): ?EArgomentoMinisteriale
    {
        return $this->argomentoLezione;
    }

    // ---------------- SETTER ----------------

    public function setAula(?EAula $aula): void
    {
        $this->aula = $aula;
    }

    public function setArgomentoLezione(
        ?EArgomentoMinisteriale $argomento
    ): void {
        $this->argomentoLezione = $argomento;
    }

    // ---------------- JSON ----------------

    public function jsonSerialize(): array
    {
        return parent::jsonSerialize() + [
            'aula' => $this->aula?->value,
            'argomentoLezione' => $this->argomentoLezione?->value
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
            ", argomento='" .
            ($this->argomentoLezione?->value ?? 'N/D') .
            '\'' .
            ", aula='" .
            ($this->aula?->value ?? 'N/D') .
            '\'' .
            '}';
    }
}
