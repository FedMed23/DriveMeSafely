<?php

namespace CamassoMedelago\DriveMeSafely\Service;

use CamassoMedelago\DriveMeSafely\Entity\EAula;
use CamassoMedelago\DriveMeSafely\Entity\EArgomentoMinisteriale;
use CamassoMedelago\DriveMeSafely\Entity\ELezionePratica;
use CamassoMedelago\DriveMeSafely\Entity\ELezioneTeoria;
use CamassoMedelago\DriveMeSafely\Entity\ELezione;
use CamassoMedelago\DriveMeSafely\Foundation\FLezione;
use Doctrine\ORM\EntityManagerInterface;

class SLezione
{
    private FLezione $fLezione;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->fLezione = new FLezione($em);
    }

    public function getPalinsesto(): array
    {
        return $this->fLezione->findAllPalinsesto();
    }

    public function inserisciPratica(\DateTimeImmutable $dataOra, string $istruttore, string $vettura): void
    {
        $this->validaData($dataOra);
        $istruttore = trim($istruttore);
        $vettura = trim($vettura);
        if ($istruttore === '' || $vettura === '') {
            throw new \InvalidArgumentException('Istruttore e vettura sono obbligatori.');
        }
        if ($this->fLezione->istruttoreVeicoloInUso($dataOra, $istruttore, $vettura)) {
            throw new \InvalidArgumentException('Istruttore o vettura già occupati.');
        }
        $this->salva(new ELezionePratica($dataOra, $istruttore, $vettura));
    }

    public function inserisciTeoria(\DateTimeImmutable $dataOra, EAula $aula, EArgomentoMinisteriale $argomento): void
    {
        $this->validaData($dataOra);
        if ($this->fLezione->aulaInUso($dataOra, $aula->value)) {
            throw new \InvalidArgumentException('L’aula è già occupata.');
        }
        $this->salva(new ELezioneTeoria($dataOra, $aula, $argomento));
    }

    private function validaData(\DateTimeImmutable $dataOra): void
    {
        $giorno = (int) $dataOra->format('N');
        $ora = (int) $dataOra->format('G');
        if ($giorno >= 6) {
            throw new \InvalidArgumentException('Non è possibile pianificare lezioni nel weekend.');
        }
        if ($ora < 8 || $ora > 18 || $ora === 13 || $ora === 14) {
            throw new \InvalidArgumentException('Orario fuori dalla fascia lavorativa.');
        }
    }

    private function salva(ELezione $lezione): void
    {
        $this->em->beginTransaction();
        try {
            $this->fLezione->save($lezione);
            $this->em->commit();
        } catch (\Throwable $e) {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->rollback();
            }
            throw $e;
        }
    }
}
