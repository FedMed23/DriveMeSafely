<?php
// src/Foundation/FPrenotazioneLezione.php
// Classe Foundation / Service per EPrenotazioneLezione

namespace CamassoMedelago\DriveMeSafely\Foundation;

use CamassoMedelago\DriveMeSafely\Entity\EPrenotazioneLezione;
use CamassoMedelago\DriveMeSafely\Entity\ELezione;
use CamassoMedelago\DriveMeSafely\Entity\ELezioneTeoria;
use CamassoMedelago\DriveMeSafely\Entity\ELezionePratica;
use CamassoMedelago\DriveMeSafely\Entity\StatoPrenotazione;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class FPrenotazioneLezione
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    // ---------------- CREATE / UPDATE ----------------

    /**
     * Salva una nuova prenotazione o aggiorna una esistente.
     */
    public function save(EPrenotazioneLezione $prenotazione): void
    {
        $this->em->persist($prenotazione);
        $this->em->flush();
    }

    /**
     * Aggiorna una prenotazione esistente.
     */
    public function update(EPrenotazioneLezione $prenotazione): void
    {
        $this->em->flush();
    }

    // ---------------- READ ----------------

    /**
     * Recupera una prenotazione tramite il suo ID.
     */
    public function findById(int $id): ?EPrenotazioneLezione
    {
        return $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->find($id);
    }

    /**
     * Recupera una prenotazione specifica per allievo e lezione, se esistente (a prescindere dallo stato).
     */
    public function findByIscrittoAndLezione(int $idIscritto, int $idLezione): ?EPrenotazioneLezione
    {
        return $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->createQueryBuilder('p')
            ->where('p.iscritto = :idIscritto')
            ->andWhere('p.lezione = :idLezione')
            ->setParameter('idIscritto', $idIscritto)
            ->setParameter('idLezione', $idLezione)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function findByIscrittoId(int $idIscritto): array
    {
        return $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->createQueryBuilder('p')
            ->join('p.lezione', 'l')
            ->where('p.iscritto = :idIscritto')
            ->setParameter('idIscritto', $idIscritto)
            ->orderBy('l.dataOra', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recupera tutte le lezioni future disponibili per un iscritto,
     * escludendo quelle per cui esiste già una prenotazione attiva.
     */
    public function findLezioniDisponibili(int $idIscritto): array
    {
        $qb = $this->em
            ->getRepository(ELezione::class)
            ->createQueryBuilder('l');

        $subQuery = $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->createQueryBuilder('p')
            ->select('IDENTITY(p.lezione)')
            ->where('p.iscritto = :idIscritto')
            ->andWhere('p.stato = :statoPrenotata')
            ->getDQL();

        $lezioni = $qb
            ->where('l.dataOra > :oggi')
            ->andWhere('l.annullata = false')
            ->andWhere($qb->expr()->notIn('l.idLezione', $subQuery))
            ->setParameter('oggi', new DateTimeImmutable())
            ->setParameter('idIscritto', $idIscritto)
            ->setParameter('statoPrenotata', \CamassoMedelago\DriveMeSafely\Entity\StatoPrenotazione::PRENOTATA)
            ->orderBy('l.dataOra', 'ASC')
            ->getQuery()
            ->getResult();

        // Una lezione teorica può avere più iscritti; una guida pratica invece
        // è uno slot individuale. Per questo togliamo dal calendario:
        // 1) Le guide pratiche che hanno già una prenotazione attiva.
        // 2) Le lezioni teoriche che hanno raggiunto la capienza massima dell'aula.
        $idGuideGiaPrenotate = array_flip($this->findIdGuidePratichePrenotate());
        $conteggioTeoria = $this->findConteggioPrenotatiPerLezioniTeoria();

        return array_values(array_filter(
            $lezioni,
            static function (ELezione $lezione) use ($idGuideGiaPrenotate, $conteggioTeoria): bool {
                if ($lezione instanceof ELezionePratica) {
                    return !isset($idGuideGiaPrenotate[$lezione->getIdLezione()]);
                }

                if ($lezione instanceof ELezioneTeoria) {
                    $aula = $lezione->getAula();
                    if ($aula !== null) {
                        $occupati = $conteggioTeoria[$lezione->getIdLezione()] ?? 0;
                        if ($occupati >= $aula->getCapienzaMassima()) {
                            return false;
                        }
                    }
                }

                return true;
            }
        ));
    }

    /**
     * Restituisce una mappa [idLezione => numeroPrenotazioniAttive] per le lezioni di teoria.
     *
     * @return array<int, int>
     */
    private function findConteggioPrenotatiPerLezioniTeoria(): array
    {
        $righe = $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->createQueryBuilder('p')
            ->select('IDENTITY(p.lezione) AS idLezione, COUNT(p.idPrenotazione) AS totale')
            ->join('p.lezione', 'l')
            ->where('p.stato = :statoPrenotata')
            ->andWhere('l INSTANCE OF ' . ELezioneTeoria::class)
            ->groupBy('p.lezione')
            ->setParameter('statoPrenotata', StatoPrenotazione::PRENOTATA)
            ->getQuery()
            ->getScalarResult();

        $mappa = [];
        foreach ($righe as $riga) {
            $mappa[(int) $riga['idLezione']] = (int) $riga['totale'];
        }

        return $mappa;
    }

    /**
     * Restituisce gli ID delle guide pratiche già assegnate a un allievo.
     * Le prenotazioni annullate non occupano più lo slot.
     *
     * @return int[]
     */
    private function findIdGuidePratichePrenotate(): array
    {
        $righe = $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->createQueryBuilder('p')
            ->select('IDENTITY(p.lezione) AS idLezione')
            ->join('p.lezione', 'l')
            ->where('p.stato = :statoPrenotata')
            ->andWhere('l INSTANCE OF ' . ELezionePratica::class)
            ->setParameter('statoPrenotata', StatoPrenotazione::PRENOTATA)
            ->getQuery()
            ->getScalarResult();

        return array_map(
            static fn (array $riga): int => (int) $riga['idLezione'],
            $righe
        );
    }

    /**
     * Indica se una guida pratica è già occupata da una prenotazione attiva.
     */
    public function isGuidaPraticaPrenotata(int $idLezione): bool
    {
        $conteggio = $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->createQueryBuilder('p')
            ->select('COUNT(p.idPrenotazione)')
            ->where('p.lezione = :idLezione')
            ->andWhere('p.stato = :statoPrenotata')
            ->setParameter('idLezione', $idLezione)
            ->setParameter('statoPrenotata', StatoPrenotazione::PRENOTATA)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $conteggio > 0;
    }

    /**
     * Recupera tutte le prenotazioni collegate a una specifica lezione,
     * escludendo quelle annullate e ordinando gli iscritti per cognome.
     */
    public function findByLezioneId(int $idLezione): array
    {
        return $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->createQueryBuilder('p')
            ->join('p.iscritto', 'i')
            ->where('p.lezione = :idLezione')
            ->andWhere('p.stato = :statoPrenotata')
            ->setParameter('idLezione', $idLezione)
            ->setParameter('statoPrenotata', StatoPrenotazione::PRENOTATA)
            ->orderBy('i.cognome', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recupera tutte le prenotazioni.
     */
    public function findAll(): array
    {
        return $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->findAll();
    }

    /**
     * Verifica se esistono prenotazioni per una lezione.
     * Se $soloAttive è true, considera solo le prenotazioni nello stato PRENOTATA.
     */
    public function haPrenotazioniPerLezione(int $idLezione, bool $soloAttive = false): bool
    {
        $qb = $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->createQueryBuilder('p')
            ->select('COUNT(p.idPrenotazione)')
            ->where('p.lezione = :idLezione')
            ->setParameter('idLezione', $idLezione);

        if ($soloAttive) {
            $qb->andWhere('p.stato = :statoPrenotata')
               ->setParameter('statoPrenotata', StatoPrenotazione::PRENOTATA);
        }

        $conteggio = $qb->getQuery()->getSingleScalarResult();

        return (int) $conteggio > 0;
    }

    /**
     * Annulla tutte le prenotazioni attive associate a una lezione.
     */
    public function annullaPrenotazioniPerLezione(int $idLezione): void
    {
        $prenotazioni = $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->createQueryBuilder('p')
            ->where('p.lezione = :idLezione')
            ->andWhere('p.stato = :statoPrenotata')
            ->setParameter('idLezione', $idLezione)
            ->setParameter('statoPrenotata', StatoPrenotazione::PRENOTATA)
            ->getQuery()
            ->getResult();

        foreach ($prenotazioni as $prenotazione) {
            /** @var EPrenotazioneLezione $prenotazione */
            $prenotazione->setStato(StatoPrenotazione::ANNULLATA);
        }

        $this->em->flush();
    }

    // ---------------- CONTROLLI ----------------

    /**
     * Verifica se un iscritto ha già una lezione prenotata
     * nello stesso giorno e orario.
     */
    public function haLezioneInOrario(
        int $idIscritto,
        DateTimeImmutable $dataOra
    ): bool {
        $conteggio = $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->createQueryBuilder('p')
            ->select('COUNT(p.idPrenotazione)')
            ->join('p.lezione', 'l')
            ->where('p.iscritto = :idIscritto')
            ->andWhere('l.dataOra = :dataOra')
            ->andWhere('p.stato = :statoPrenotata')
            ->setParameter('idIscritto', $idIscritto)
            ->setParameter('dataOra', $dataOra)
            ->setParameter('statoPrenotata', StatoPrenotazione::PRENOTATA)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $conteggio > 0;
    }

    /**
     * Verifica se una lezione teorica ha raggiunto
     * la capienza massima dell'aula.
     */
    public function isAulaPiena(
        int $idLezione,
        int $capienzaMassima
    ): bool {
        $iscrittiPrenotati = $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->createQueryBuilder('p')
            ->select('COUNT(p.idPrenotazione)')
            ->where('p.lezione = :idLezione')
            ->andWhere('p.stato = :statoPrenotata')
            ->setParameter('idLezione', $idLezione)
            ->setParameter('statoPrenotata', StatoPrenotazione::PRENOTATA)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $iscrittiPrenotati >= $capienzaMassima;
    }

    /**
     * Verifica se un istruttore è disponibile in un determinato orario.
     * (Per lezioni pratiche)
     */
    public function isIstruttoreDisponibile(
        string $nomeIstruttore,
        \DateTimeImmutable $dataOra
    ): bool {
        $conflitti = $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->createQueryBuilder('p')
            ->select('COUNT(p.idPrenotazione)')
            ->join('p.lezione', 'l')
            ->where('l INSTANCE OF ' . ELezionePratica::class)
            ->andWhere('l.istruttore = :istruttore')
            ->andWhere('l.dataOra = :dataOra')
            ->andWhere('p.stato = :statoPrenotata')
            ->setParameter('istruttore', $nomeIstruttore)
            ->setParameter('dataOra', $dataOra)
            ->setParameter('statoPrenotata', StatoPrenotazione::PRENOTATA)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $conflitti === 0;
    }

    /**
     * Verifica se una vettura è disponibile in un determinato orario.
     * (Per lezioni pratiche)
     */
    public function isVetturaDisponibile(
        string $targa,
        \DateTimeImmutable $dataOra
    ): bool {
        $conflitti = $this->em
            ->getRepository(EPrenotazioneLezione::class)
            ->createQueryBuilder('p')
            ->select('COUNT(p.idPrenotazione)')
            ->join('p.lezione', 'l')
            ->where('l INSTANCE OF ' . ELezionePratica::class)
            ->andWhere('l.vettura = :vettura')
            ->andWhere('l.dataOra = :dataOra')
            ->andWhere('p.stato = :statoPrenotata')
            ->setParameter('vettura', $targa)
            ->setParameter('dataOra', $dataOra)
            ->setParameter('statoPrenotata', StatoPrenotazione::PRENOTATA)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $conflitti === 0;
    }

    /**
     * Alias per retrocompatibilità.
     */
    public function isVetturDisponibile(
        string $targa,
        \DateTimeImmutable $dataOra
    ): bool {
        return $this->isVetturaDisponibile($targa, $dataOra);
    }


    // ---------------- DELETE ----------------

    /**
     * Elimina una prenotazione.
     */
    public function delete(EPrenotazioneLezione $prenotazione): void
    {
        $this->em->remove($prenotazione);
        $this->em->flush();
    }
}

