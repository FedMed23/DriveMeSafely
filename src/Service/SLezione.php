<?php

namespace CamassoMedelago\DriveMeSafely\Service;

use CamassoMedelago\DriveMeSafely\Entity\EAula;
use CamassoMedelago\DriveMeSafely\Entity\EArgomentoMinisteriale;
use CamassoMedelago\DriveMeSafely\Entity\ELezionePratica;
use CamassoMedelago\DriveMeSafely\Entity\ELezioneTeoria;
use CamassoMedelago\DriveMeSafely\Entity\ELezione;
use CamassoMedelago\DriveMeSafely\Foundation\FLezione;
use CamassoMedelago\DriveMeSafely\Foundation\FPrenotazioneLezione;
use Doctrine\ORM\EntityManagerInterface;

//Service che implementa la logica per inserire e eliminare le lezioni dal palinsesto
class SLezione
{
    private FLezione $fLezione;
    private FPrenotazioneLezione $fPrenotazione;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->fLezione = new FLezione($em);
        $this->fPrenotazione = new FPrenotazioneLezione($em);
    }

    //1)Metodo che riporta l'intero palinsesto delle lezioni
    public function getPalinsesto(): array
    {
        return $this->fLezione->findAllPalinsesto();
    }

    /**
     * Elenco degli istruttori già usati, da proporre come suggerimento
     * nel form di inserimento (evita refusi/duplicati per battitura).
     */
    public function getIstruttoriSuggeriti(): array
    {
        return $this->fLezione->findIstruttoriDistinti();
    }

    /**
     * Elenco delle vetture già usate, da proporre come suggerimento
     * nel form di inserimento (evita refusi/duplicati per battitura).
     */
    public function getVettureSuggerite(): array
    {
        return $this->fLezione->findVettureDistinte();
    }

    //2)Metodo che inserisce una lezione nel palinsesto
    public function inserisciLezione(array $dati): ELezione
    {

        if (trim((string) ($dati['dataOra'] ?? '')) === '') {
            throw new \InvalidArgumentException('Inserisci una data e un orario validi.');
        }

        //Serve per problemi di parsing della data e ora
        try {
            $dataOra = new \DateTimeImmutable((string) $dati['dataOra']);
        } catch (\Throwable) {
            throw new \InvalidArgumentException('Inserisci una data e un orario validi.');
        }

        $this->validaData($dataOra);
        $tipo = strtoupper(trim((string) ($dati['tipoLezione'] ?? '')));

        //Controllo sul tipo di lezione e controllo sui campi specifici per ciascun tipo
        if ($tipo === 'PRATICA') {
            $istruttoreRaw = (string) ($dati['istruttore'] ?? '');
            $vetturaRaw = (string) ($dati['vettura'] ?? '');

            // Normalizzazione: rimozione spazi multipli e trim
            $istruttore = preg_replace('/\s+/', ' ', trim($istruttoreRaw));
            // Targa: rimozione caratteri non alfanumerici, trattini o spazi e maiuscolo
            $vettura = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $vetturaRaw));

            if ($istruttore === '' || $vettura === '') {
                throw new \InvalidArgumentException('Istruttore e vettura sono obbligatori per una guida pratica.');
            }
            if (mb_strlen($istruttore) < 2 || mb_strlen($istruttore) > 100) {
                throw new \InvalidArgumentException('Nome istruttore non valido.');
            }
            if (strlen($vettura) < 5 || strlen($vettura) > 10) {
                throw new \InvalidArgumentException('Targa vettura non valida (deve contenere tra 5 e 10 caratteri alfanumerici).');
            }
            if ($this->fLezione->istruttoreVeicoloInUso($dataOra, $istruttore, $vettura)) {
                throw new \InvalidArgumentException('Istruttore o vettura già occupati in questa data e fascia oraria.');
            }
            return new ELezionePratica($dataOra, $istruttore, $vettura);
        }

        //Se il tipo è teoria, controllo aula e argomento ministeriale
        if ($tipo === 'TEORIA') {
            $aula = EAula::tryFrom((string) ($dati['aula'] ?? ''));
            $argomento = EArgomentoMinisteriale::tryFrom((string) ($dati['argomento'] ?? ''));
            if ($aula === null || $argomento === null) {
                throw new \InvalidArgumentException('Aula e argomento sono obbligatori per una lezione di teoria.');
            }
            if ($this->fLezione->aulaInUso($dataOra, $aula->value)) {
                throw new \InvalidArgumentException('L’aula è già occupata in questa data e fascia oraria.');
            }
            return new ELezioneTeoria($dataOra, $aula, $argomento);
        }

        throw new \InvalidArgumentException('Tipo di lezione non valido.');
    }

    //3)Metodo che conferma l'inserimento di una lezione nel palinsesto
    public function confermaLezione(ELezione $lezione): void
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
    //4)Metodo che annulla una lezione dal palinsesto (vedi annullaLezione)

    /**
     * Annulla una lezione futura (e tutte le relative prenotazioni attive degli allievi).
     */
    public function annullaLezione(int $idLezione): void
    {
        $this->em->beginTransaction();
        try {
            $lezione = $this->fLezione->findByIdForUpdate($idLezione);
            if ($lezione === null) {
                throw new \InvalidArgumentException('Lezione non trovata.');
            }
            if ($lezione->isAnnullata()) {
                throw new \InvalidArgumentException('La lezione è già stata annullata.');
            }
            if ($lezione->getDataOra() < new \DateTimeImmutable()) {
                throw new \InvalidArgumentException('Non è possibile annullare lezioni già svolte nel passato.');
            }

            $lezione->setAnnullata(true);

            // Annulla tutte le prenotazioni attive collegate
            $this->fPrenotazione->annullaPrenotazioniPerLezione($idLezione);
            $this->em->commit();
        } catch (\Throwable $e) {
            if ($this->em->getConnection()->isTransactionActive()) {
                $this->em->rollback();
            }
            throw $e;
        }
    }

    //Metodi ausiliari
    private function validaData(\DateTimeImmutable $dataOra): void
    {
        $adesso = new \DateTimeImmutable();
        if ($dataOra <= $adesso) {
            throw new \InvalidArgumentException('Non è possibile pianificare lezioni nel passato o nel momento corrente.');
        }

        // Limite massimo di pianificazione: massimo 1 anno nel futuro
        $limiteMassimo = $adesso->modify('+1 year');
        if ($dataOra > $limiteMassimo) {
            throw new \InvalidArgumentException('Non è possibile pianificare lezioni con oltre 1 anno di anticipo.');
        }

        $minuti = (int) $dataOra->format('i');
        $secondi = (int) $dataOra->format('s');
        if (!in_array($minuti, [0, 30], true) || $secondi !== 0) {
            throw new \InvalidArgumentException('Gli slot delle lezioni devono essere allineati all’ora esatta o alla mezz’ora (es. 09:00 o 09:30).');
        }

        if ($this->isGiornoFestivo($dataOra)) {
            throw new \InvalidArgumentException('Non è possibile pianificare lezioni nei giorni festivi.');
        }

        $giorno = (int) $dataOra->format('N');
        if ($giorno >= 6) {
            throw new \InvalidArgumentException('Non è possibile pianificare lezioni nel weekend (sabato o domenica).');
        }

        $ora = (int) $dataOra->format('G');
        // Fasce lavorative consentite (lezioni di 1 ora): 08:00 - 13:00 (ultimo slot mattutino 12:00/12:30) e 14:00 - 19:00 (ultimo slot pomeridiano 18:00)
        // La pausa pranzo è dalle 13:00 alle 14:00.
        if ($ora < 8 || $ora > 18) {
            throw new \InvalidArgumentException('Orario fuori dalla fascia lavorativa dell’autoscuola (08:00 - 19:00).');
        }

        // Se l'ora è 13 (pausa pranzo), non è consentito
        if ($ora === 13) {
            throw new \InvalidArgumentException('Orario non disponibile: pausa pranzo dell’autoscuola (13:00 - 14:00).');
        }

        // Se l'inizio è alle 12:30, la lezione terminerebbe alle 13:30 (sconfinando nella pausa pranzo)
        if ($ora === 12 && $minuti > 0) {
            throw new \InvalidArgumentException('La lezione non può sconfinare nella pausa pranzo (13:00 - 14:00).');
        }

        // Se l'inizio è alle 18:30, la lezione terminerebbe alle 19:30 (sconfinando oltre l'orario di chiusura 19:00)
        if ($ora === 18 && $minuti > 0) {
            throw new \InvalidArgumentException('La lezione non può sconfinare oltre l’orario di chiusura (19:00).');
        }
    }

    /**
     * Verifica se la data indicata cade in un giorno festivo (festività
     * civili/religiose fisse, oppure Pasqua e Lunedì dell'Angelo, che
     * cambiano data di anno in anno).
     */
    private function isGiornoFestivo(\DateTimeImmutable $data): bool
    {
        $meseGiorno = $data->format('m-d');

        // Festività fisse: Capodanno, Epifania (Befana), Festa della Liberazione,
        // Festa dei Lavoratori, Festa della Repubblica, Ferragosto, Ognissanti,
        // Immacolata Concezione, Natale, Santo Stefano.
        $festivitaFisse = [
            '01-01', // Capodanno
            '01-06', // Epifania (Befana)
            '04-25', // Festa della Liberazione
            '05-01', // Festa dei Lavoratori
            '06-02', // Festa della Repubblica
            '08-15', // Ferragosto
            '11-01', // Ognissanti
            '12-08', // Immacolata Concezione
            '12-25', // Natale
            '12-26', // Santo Stefano
        ];

        if (in_array($meseGiorno, $festivitaFisse, true)) {
            return true;
        }

        // Pasqua e Lunedì dell'Angelo: la data di Pasqua varia ogni anno.
        $anno = (int) $data->format('Y');
        $giornoPasqua = easter_date($anno);
        $pasqua = (new \DateTimeImmutable('@' . $giornoPasqua))->setTime(0, 0);
        $lunediAngelo = $pasqua->modify('+1 day');

        $giornoData = $data->setTime(0, 0);

        return $giornoData == $pasqua || $giornoData == $lunediAngelo;
    }

}
