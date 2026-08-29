<?php

namespace CamassoMedelago\DriveMeSafely\Service;

use CamassoMedelago\DriveMeSafely\Entity\EDipendente;
use CamassoMedelago\DriveMeSafely\Entity\EEsame;
use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Entity\EPrenotazioneEsami;
use CamassoMedelago\DriveMeSafely\Entity\TipologiaEsame;
use CamassoMedelago\DriveMeSafely\Foundation\FEsame;
use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FPrenotazioneEsami;
use CamassoMedelago\DriveMeSafely\Foundation\FQuiz;
use CamassoMedelago\DriveMeSafely\Foundation\FSvolgimentoQuiz;
use CamassoMedelago\DriveMeSafely\Foundation\FUtenteRegistrato;
use Doctrine\ORM\EntityManagerInterface;

class SPrenotazioneEsame
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function getCalendario(): array
    {
        $f = new FPrenotazioneEsami($this->em);
        return ['storicoPrenotazioni' => $f->findAll(), 'esamiDisponibili' => (new FEsame($this->em))->findSessioniFuture()];
    }

    public function getIscrittiIdonei(int $idEsame): array
    {
        $esame = (new FEsame($this->em))->findById($idEsame);
        if (!$esame) throw new \InvalidArgumentException('Esame non trovato.');
        $quiz = new FQuiz($this->em);
        $svolgimenti = new FSvolgimentoQuiz($this->em);
        $prenotazioni = new FPrenotazioneEsami($this->em);
        $totali = $quiz->contaQuiz();
        $risultato = [];
        foreach ((new FIscritto($this->em))->findAttivi() as $iscritto) {
            $svolti = $svolgimenti->contaQuizSvoltiByIscritto($iscritto->getId());
            $superati = $svolgimenti->contaQuizSuperatiByIscritto($iscritto->getId());
            if ($totali === 0 || $svolti * 100 < $totali * 70 || $svolti === 0 || $superati * 100 < $svolti * 70) continue;
            if ($esame->getTipologia() === TipologiaEsame::PRATICA && !$prenotazioni->haSuperatoEsameTeorico($iscritto->getId())) continue;
            if (!$prenotazioni->haEsameInOrario($iscritto->getId(), \DateTimeImmutable::createFromMutable($esame->getDataEs()))) $risultato[] = $iscritto;
        }
        return $risultato;
    }

    public function prenota(int $idDipendente, int $idEsame, array $idIscritti): array
    {
        if ($idIscritti === []) throw new \InvalidArgumentException('È necessario selezionare almeno un iscritto.');
        $dipendente = (new FUtenteRegistrato($this->em))->getById($idDipendente);
        $esame = (new FEsame($this->em))->findById($idEsame);
        if (!$dipendente instanceof EDipendente) throw new \InvalidArgumentException('Dipendente non trovato nel sistema.');
        if (!$esame || $esame->getDataEs() < new \DateTime()) throw new \InvalidArgumentException('Lo slot selezionato non è più disponibile.');
        $fIscritto = new FIscritto($this->em); $fPrenotazioni = new FPrenotazioneEsami($this->em); $result = [];
        foreach ($idIscritti as $id) {
            $iscritto = $fIscritto->findById((int) $id);
            if (!$iscritto) throw new \InvalidArgumentException("Allievo con ID $id non trovato.");
            if ($fPrenotazioni->haEsameInOrario($iscritto->getId(), \DateTimeImmutable::createFromMutable($esame->getDataEs()))) throw new \InvalidArgumentException("L'allievo {$iscritto->getNome()} {$iscritto->getCognome()} ha già un esame nello stesso orario.");
            if ($esame->getTipologia() === TipologiaEsame::PRATICA && !in_array($iscritto, $this->getIscrittiIdonei($idEsame), true)) throw new \InvalidArgumentException('L\'allievo non è idoneo alla prova pratica.');
            $p = new EPrenotazioneEsami(); $p->init($dipendente, $esame, $iscritto, 'PRENOTATO'); $result[] = $p;
        }
        return $result;
    }

    public function conferma(array $prenotazioni): void
    {
        $this->em->beginTransaction();
        try { foreach ($prenotazioni as $p) $this->em->persist($p); $this->em->flush(); $this->em->commit(); }
        catch (\Throwable $e) { if ($this->em->getConnection()->isTransactionActive()) $this->em->rollback(); throw $e; }
    }
}
