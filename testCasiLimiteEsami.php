<?php

namespace CamassoMedelago\DriveMeSafely;

require_once __DIR__ . '/vendor/autoload.php';

use CamassoMedelago\DriveMeSafely\Entity\EDipendente;
use CamassoMedelago\DriveMeSafely\Entity\EEsame;
use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Entity\EPrenotazioneEsami;
use CamassoMedelago\DriveMeSafely\Entity\StatoPrenotazioneEsame;
use CamassoMedelago\DriveMeSafely\Entity\TipologiaEsame;
use CamassoMedelago\DriveMeSafely\Foundation\FEsame;
use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FPrenotazioneEsami;
use CamassoMedelago\DriveMeSafely\Foundation\FPrenotazioneLezione;
use CamassoMedelago\DriveMeSafely\Foundation\FQuiz;
use CamassoMedelago\DriveMeSafely\Foundation\FSvolgimentoQuiz;
use CamassoMedelago\DriveMeSafely\Foundation\FUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\Service\SPrenotazioneEsame;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use DateTimeImmutable;

class TestCasiLimiteEsami
{
    private SPrenotazioneEsame $service;
    public array $iscrittiStore = [];
    public array $esamiStore = [];
    public array $prenotazioniStore = [];
    public array $dipendentiStore = [];
    public array $lezioniPrenotateStore = []; // [idIscritto => [dataOraString]]
    public int $totaleQuizDisponibili = 10;
    public array $quizSvoltiPerIscritto = [];
    public array $quizSuperatiPerIscritto = [];

    private int $passati = 0;
    private int $totali = 0;

    public function __construct()
    {
        $this->inizializzaMockService();
    }

    private function inizializzaMockService(): void
    {
        $test = $this;

        $emMock = new class implements EntityManagerInterface {
            public function beginTransaction() {}
            public function commit() {}
            public function rollback() {}
            public function flush($entity = null) {}
            public function persist($object) {}
            public function remove($object) {}
            public function find($className, $id, $lockMode = null, $lockVersion = null) { return null; }
            public function clear($objectName = null) {}
            public function close() {}
            public function copy($entity, $deep = false) {}
            public function refresh($object) {}
            public function detach($object) {}
            public function merge($object) { return $object; }
            public function initializeObject($obj) {}
            public function contains($object) { return true; }
            public function getRepository($className) { return null; }
            public function getClassMetadata($className) { return null; }
            public function getMetadataFactory() { return null; }
            public function getConnection() {
                return new class {
                    public function isTransactionActive() { return false; }
                };
            }
            public function getExpressionBuilder() { return null; }
            public function createQuery($dql = '') { return null; }
            public function createNamedQuery($name) { return null; }
            public function createNativeQuery($sql, $rsm) { return null; }
            public function createNamedNativeQuery($name) { return null; }
            public function createQueryBuilder() { return null; }
            public function getReference($entityName, $id) { return null; }
            public function getPartialReference($entityName, $identifier) { return null; }
            public function getCache() { return null; }
            public function isFiltersStateClean() { return true; }
            public function hasFilters() { return false; }
            public function getFilters() { return null; }
            public function getUnitOfWork() { return null; }
            public function getHydrator($hydrationMode) { return null; }
            public function newHydrator($hydrationMode) { return null; }
            public function getProxyFactory() { return null; }
            public function getEventManager() { return null; }
            public function getConfiguration() { return null; }
            public function isOpen() { return true; }
            public function lock($entity, $lockMode, $lockVersion = null) {}
            public function transactional($func) { return $func($this); }
            public function wrapInTransaction(callable $func): mixed { return $func($this); }
        };

        $this->service = new SPrenotazioneEsame($emMock);

        // Mock FEsame
        $mockFEsame = new class($emMock, $test) extends FEsame {
            public function __construct($em, private $test) { parent::__construct($em); }
            public function findById(int $id): ?EEsame {
                return $this->test->esamiStore[$id] ?? null;
            }
            public function findSessioniFuture(): array {
                $now = new DateTime();
                return array_filter($this->test->esamiStore, fn(EEsame $e) => $e->getDataEs() > $now);
            }
        };

        // Mock FIscritto
        $mockFIscritto = new class($emMock, $test) extends FIscritto {
            public function __construct($em, private $test) { parent::__construct($em); }
            public function findById(int $id): ?EIscritto {
                return $this->test->iscrittiStore[$id] ?? null;
            }
            public function findAttivi(): array {
                return array_filter($this->test->iscrittiStore, fn(EIscritto $i) => $i->isStatoUtente());
            }
        };

        // Mock FUtenteRegistrato
        $mockFDipendente = new class($emMock, $test) extends FUtenteRegistrato {
            public function __construct($em, private $test) { parent::__construct($em); }
            public function getById(int $id): ?\CamassoMedelago\DriveMeSafely\Entity\EUtenteRegistrato {
                return $this->test->dipendentiStore[$id] ?? null;
            }
        };

        // Mock FPrenotazioneEsami
        $mockFPrenotazioni = new class($emMock, $test) extends FPrenotazioneEsami {
            public function __construct($em, private $test) { parent::__construct($em); }
            public function findById(int $id): ?EPrenotazioneEsami {
                return $this->test->prenotazioniStore[$id] ?? null;
            }
            public function findAll(): array {
                return array_values($this->test->prenotazioniStore);
            }
            public function save(EPrenotazioneEsami $p): void {
                $id = $p->getIdPrenotazioneEsame() ?? (count($this->test->prenotazioniStore) + 1);
                $p->setIdPrenotazioneEsame($id);
                $this->test->prenotazioniStore[$id] = $p;
            }
            public function isIscrittoGiaPrenotatoAdEsame(int $idIscritto, int $idEsame): bool {
                foreach ($this->test->prenotazioniStore as $p) {
                    if ($p->getAllievo()->getId() === $idIscritto &&
                        $p->getEsame()->getIdEsame() === $idEsame &&
                        $p->getStato() === StatoPrenotazioneEsame::PRENOTATO) {
                        return true;
                    }
                }
                return false;
            }
            public function haSuperatoEsameTeorico(int $idIscritto): bool {
                foreach ($this->test->prenotazioniStore as $p) {
                    if ($p->getAllievo()->getId() === $idIscritto &&
                        $p->getEsame()->getTipologia() === TipologiaEsame::TEORIA &&
                        $p->isSuperato()) {
                        return true;
                    }
                }
                return false;
            }
            public function haSuperatoEsamePratico(int $idIscritto): bool {
                foreach ($this->test->prenotazioniStore as $p) {
                    if ($p->getAllievo()->getId() === $idIscritto &&
                        $p->getEsame()->getTipologia() === TipologiaEsame::PRATICA &&
                        $p->isSuperato()) {
                        return true;
                    }
                }
                return false;
            }
            public function haEsameInOrario(int $idIscritto, DateTimeImmutable $dataOra): bool {
                foreach ($this->test->prenotazioniStore as $p) {
                    if ($p->getAllievo()->getId() === $idIscritto &&
                        $p->getStato() === StatoPrenotazioneEsame::PRENOTATO) {
                        $pData = $p->getEsame()->getDataEs()->format('Y-m-d H:i');
                        if ($pData === $dataOra->format('Y-m-d H:i')) {
                            return true;
                        }
                    }
                }
                return false;
            }
            public function haPrenotazioneFuturaAttivaPerTipologia(int $idIscritto, $tipologia): bool {
                $tipo = $tipologia instanceof TipologiaEsame ? $tipologia : TipologiaEsame::tryFrom($tipologia);
                $now = new DateTime();
                foreach ($this->test->prenotazioniStore as $p) {
                    if ($p->getAllievo()->getId() === $idIscritto &&
                        $p->getStato() === StatoPrenotazioneEsame::PRENOTATO &&
                        $p->getEsame()->getTipologia() === $tipo &&
                        $p->getEsame()->getDataEs() > $now) {
                        return true;
                    }
                }
                return false;
            }
        };

        // Mock FQuiz
        $mockFQuiz = new class($emMock, $test) extends FQuiz {
            public function __construct($em, private $test) { parent::__construct($em); }
            public function contaQuiz(): int {
                return $this->test->totaleQuizDisponibili;
            }
        };

        // Mock FSvolgimentoQuiz
        $mockFSvolgimenti = new class($emMock, $test) extends FSvolgimentoQuiz {
            public function __construct($em, private $test) { parent::__construct($em); }
            public function contaQuizSvoltiByIscritto(?int $id): int {
                return $this->test->quizSvoltiPerIscritto[$id] ?? 0;
            }
            public function contaQuizSuperatiByIscritto(?int $idIscritto): int {
                return $this->test->quizSuperatiPerIscritto[$idIscritto] ?? 0;
            }
        };

        // Mock FPrenotazioneLezione
        $mockFPrenotazioneLezione = new class($emMock, $test) extends FPrenotazioneLezione {
            public function __construct($em, private $test) { parent::__construct($em); }
            public function haLezioneInOrario(int $idIscritto, DateTimeImmutable $dataOra): bool {
                $lezioni = $this->test->lezioniPrenotateStore[$idIscritto] ?? [];
                return in_array($dataOra->format('Y-m-d H:i'), $lezioni, true);
            }
        };

        // Iniezione con reflection
        $ref = new \ReflectionClass(SPrenotazioneEsame::class);
        $pEsame = $ref->getProperty('fEsame');
        $pEsame->setAccessible(true);
        $pEsame->setValue($this->service, $mockFEsame);

        $pIscritto = $ref->getProperty('fIscritto');
        $pIscritto->setAccessible(true);
        $pIscritto->setValue($this->service, $mockFIscritto);

        $pDip = $ref->getProperty('fDipendente');
        $pDip->setAccessible(true);
        $pDip->setValue($this->service, $mockFDipendente);

        $pPren = $ref->getProperty('fPrenotazioni');
        $pPren->setAccessible(true);
        $pPren->setValue($this->service, $mockFPrenotazioni);

        $pPrenLez = $ref->getProperty('fPrenotazioneLezione');
        $pPrenLez->setAccessible(true);
        $pPrenLez->setValue($this->service, $mockFPrenotazioneLezione);

        $pQ = $ref->getProperty('fQuiz');
        $pQ->setAccessible(true);
        $pQ->setValue($this->service, $mockFQuiz);

        $pS = $ref->getProperty('fSvolgimenti');
        $pS->setAccessible(true);
        $pS->setValue($this->service, $mockFSvolgimenti);
    }

    private function test(string $descrizione, callable $callback): void
    {
        $this->totali++;
        try {
            $callback();
            $this->passati++;
            echo "  [PASS] {$descrizione}\n";
        } catch (\Throwable $e) {
            echo "  [FAIL] {$descrizione} -> " . $e->getMessage() . "\n";
        }
    }

    private function creaIscritto(int $id, string $nome, string $cognome): EIscritto
    {
        $patente = new \CamassoMedelago\DriveMeSafely\Entity\EPatente('B', 'Patente B');
        $iscritto = new EIscritto(
            $nome,
            $cognome,
            strtolower($nome . '.' . $cognome . $id . '@test.it'),
            strtolower($nome . '_' . $cognome . $id),
            'Password123!',
            true,
            'RSSMRA00A01H501U',
            new DateTimeImmutable('-20 years'),
            'Roma',
            'Via Roma 1',
            '+39 3331234567',
            $patente
        );

        $refUtente = new \ReflectionClass(EIscritto::class);
        $parent = $refUtente->getParentClass();
        $propId = $parent->getProperty('id');
        $propId->setAccessible(true);
        $propId->setValue($iscritto, $id);

        return $iscritto;
    }

    public function runAll(): void
    {
        echo "====================================================================\n";
        echo " AVVIO TEST SUITE: Flusso Prenotazione Esami Segreteria\n";
        echo "====================================================================\n\n";

        // Setup base
        $dipendente = new EDipendente('Segreteria', 'AutoScuola', 'segreteria@test.it', 'segreteria', 'Pass123!', true, 'Segretaria', 1500.0);
        $refDip = new \ReflectionClass(EDipendente::class);
        $propDipId = $refDip->getParentClass()->getProperty('id');
        $propDipId->setAccessible(true);
        $propDipId->setValue($dipendente, 1);
        $this->dipendentiStore[1] = $dipendente;

        $iscrittoIdoneoTeoria = $this->creaIscritto(101, 'Mario', 'Rossi');
        $this->iscrittiStore[101] = $iscrittoIdoneoTeoria;
        // 8 quiz svolti su 10, tutti e 8 superati (>= 70%)
        $this->quizSvoltiPerIscritto[101] = 8;
        $this->quizSuperatiPerIscritto[101] = 8;

        $iscrittoNonIdoneoQuiz = $this->creaIscritto(102, 'Luca', 'Bianchi');
        $this->iscrittiStore[102] = $iscrittoNonIdoneoQuiz;
        // Solo 3 quiz svolti (< 70%)
        $this->quizSvoltiPerIscritto[102] = 3;
        $this->quizSuperatiPerIscritto[102] = 3;

        $iscrittoGiaSuperatoTeoria = $this->creaIscritto(103, 'Anna', 'Verdi');
        $this->iscrittiStore[103] = $iscrittoGiaSuperatoTeoria;
        $this->quizSvoltiPerIscritto[103] = 9;
        $this->quizSuperatiPerIscritto[103] = 9;

        $esameTeoriaPassato = new EEsame();
        $esameTeoriaPassato->setIdEsame(1);
        $esameTeoriaPassato->init(TipologiaEsame::TEORIA, new DateTime('-10 days'));
        $this->esamiStore[1] = $esameTeoriaPassato;

        // Anna ha superato l'esame teorico
        $prenSuperata = new EPrenotazioneEsami();
        $prenSuperata->setIdPrenotazioneEsame(1);
        $prenSuperata->init($dipendente, $esameTeoriaPassato, $iscrittoGiaSuperatoTeoria, StatoPrenotazioneEsame::SUPERATO);
        $prenSuperata->setSuperato(true);
        $this->prenotazioniStore[1] = $prenSuperata;

        $esameTeoriaFuturo = new EEsame();
        $esameTeoriaFuturo->setIdEsame(2);
        $esameTeoriaFuturo->init(TipologiaEsame::TEORIA, new DateTime('+5 days 10:00'));
        $this->esamiStore[2] = $esameTeoriaFuturo;

        $esamePraticaFuturo = new EEsame();
        $esamePraticaFuturo->setIdEsame(3);
        $esamePraticaFuturo->init(TipologiaEsame::PRATICA, new DateTime('+7 days 15:00'));
        $this->esamiStore[3] = $esamePraticaFuturo;

        // TEST 1: Esclusione allievo che ha già superato la teoria da lista idonei teoria
        $this->test("1. Allievo che ha già superato la teoria non figura tra gli idonei per esame di teoria", function() {
            $idonei = $this->service->getIscrittiIdonei(2);
            $ids = array_map(fn($i) => $i->getId(), $idonei);
            if (in_array(103, $ids)) {
                throw new \Exception("Allievo 103 (che ha già superato la teoria) è presente tra gli idonei.");
            }
            if (!in_array(101, $ids)) {
                throw new \Exception("Allievo 101 idoneo dovrebbe essere presente.");
            }
        });

        // TEST 2: Prenotazione non consentita per allievo che ha già superato l'esame della stessa tipologia
        $this->test("2. Errore se si tenta di prenotare per la teoria un allievo che l'ha già superata", function() {
            try {
                $this->service->prenota(1, 2, [103]);
                throw new \Exception("Doveva lanciare eccezione per teoria già superata.");
            } catch (\InvalidArgumentException $e) {
                if (!str_contains($e->getMessage(), 'ha già superato l\'esame di teoria')) {
                    throw new \Exception("Messaggio inatteso: " . $e->getMessage());
                }
            }
        });

        // TEST 3: Prenotazione consentita per allievo idoneo
        $this->test("3. Prenotazione con successo di Mario Rossi (101) alla sessione di teoria", function() {
            $pren = $this->service->prenota(1, 2, [101]);
            if (count($pren) !== 1) {
                throw new \Exception("Attesa 1 prenotazione, ottenute: " . count($pren));
            }
            $p = $pren[0];
            $p->setIdPrenotazioneEsame(10);
            $this->prenotazioniStore[10] = $p;
        });

        // TEST 4: Esclusione allievo già prenotato alla sessione dagli idonei
        $this->test("4. Allievo già prenotato alla sessione non figura più tra gli idonei", function() {
            $idonei = $this->service->getIscrittiIdonei(2);
            $ids = array_map(fn($i) => $i->getId(), $idonei);
            if (in_array(101, $ids)) {
                throw new \Exception("Mario Rossi non deve figurare tra gli idonei dopo essere stato prenotato.");
            }
        });

        // TEST 5: Tentativo di re-iscrizione alla stessa sessione genera eccezione
        $this->test("5. Errore se si ritenta di prenotare un allievo già prenotato alla stessa sessione", function() {
            try {
                $this->service->prenota(1, 2, [101]);
                throw new \Exception("Doveva lanciare eccezione per già prenotato.");
            } catch (\InvalidArgumentException $e) {
                if (!str_contains($e->getMessage(), 'è già prenotato a questa sessione')) {
                    throw new \Exception("Messaggio inatteso: " . $e->getMessage());
                }
            }
        });

        // TEST 6: Allievo che non ha superato la teoria non può accedere all'esame pratico
        $this->test("6. Mario Rossi non può prenotarsi all'esame pratico perché non ha ancora superato la teoria", function() {
            $idonei = $this->service->getIscrittiIdonei(3);
            $ids = array_map(fn($i) => $i->getId(), $idonei);
            if (in_array(101, $ids)) {
                throw new \Exception("Mario Rossi non dovrebbe essere idoneo alla pratica.");
            }

            try {
                $this->service->prenota(1, 3, [101]);
                throw new \Exception("Doveva lanciare eccezione per idoneità pratica.");
            } catch (\InvalidArgumentException $e) {
                if (!str_contains($e->getMessage(), 'non è idoneo alla prova pratica')) {
                    throw new \Exception("Messaggio inatteso: " . $e->getMessage());
                }
            }
        });

        // TEST 7: Anna (che ha superato la teoria) è idonea per la pratica
        $this->test("7. Anna Verdi (che ha superato la teoria e quiz >= 70%) figura tra gli idonei alla pratica", function() {
            $idonei = $this->service->getIscrittiIdonei(3);
            $ids = array_map(fn($i) => $i->getId(), $idonei);
            if (!in_array(103, $ids)) {
                throw new \Exception("Anna Verdi deve essere idonea per l'esame pratico.");
            }
        });

        // TEST 8: Annullamento prenotazione esame attiva
        $this->test("8. Annullamento prenotazione attiva (id=10) da parte della segreteria", function() {
            $this->service->annullaPrenotazione(10);
            $pren = $this->prenotazioniStore[10];
            if ($pren->getStato() !== StatoPrenotazioneEsame::ANNULLATO) {
                throw new \Exception("Stato prenotazione non impostato su ANNULLATO: " . $pren->getStato()->value);
            }
        });

        // TEST 9: Dopo l'annullamento, l'allievo ritorna idoneo e prenotabile
        $this->test("9. Dopo l'annullamento della prenotazione, Mario Rossi torna tra gli idonei per la teoria", function() {
            $idonei = $this->service->getIscrittiIdonei(2);
            $ids = array_map(fn($i) => $i->getId(), $idonei);
            if (!in_array(101, $ids)) {
                throw new \Exception("Mario Rossi deve figurare di nuovo tra gli idonei dopo l'annullamento.");
            }
        });

        // TEST 10: Impossibile annullare una prenotazione già annullata o non attiva
        $this->test("10. Errore nel tentativo di ri-annullare una prenotazione già annullata", function() {
            try {
                $this->service->annullaPrenotazione(10);
                throw new \Exception("Doveva lanciare LogicException.");
            } catch (\LogicException $e) {
                if (!str_contains($e->getMessage(), 'non è in stato attivo')) {
                    throw new \Exception("Messaggio inatteso: " . $e->getMessage());
                }
            }
        });

        // TEST 11: Impossibile annullare un esame passato
        $this->test("11. Errore nel tentativo di annullare un esame con data passata", function() use ($dipendente, $esameTeoriaPassato, $iscrittoIdoneoTeoria) {
            $prenPassata = new EPrenotazioneEsami();
            $prenPassata->setIdPrenotazioneEsame(11);
            $prenPassata->init($dipendente, $esameTeoriaPassato, $iscrittoIdoneoTeoria, StatoPrenotazioneEsame::PRENOTATO);
            $this->prenotazioniStore[11] = $prenPassata;

            try {
                $this->service->annullaPrenotazione(11);
                throw new \Exception("Doveva lanciare LogicException per data passata.");
            } catch (\LogicException $e) {
                if (!str_contains($e->getMessage(), 'già iniziato o passato')) {
                    throw new \Exception("Messaggio inatteso: " . $e->getMessage());
                }
            }
        });

        // TEST 12: Validazione input errati su prenota
        $this->test("12. Validazione ID dipendente o ID esame non validi", function() {
            try {
                $this->service->prenota(0, 2, [101]);
                throw new \Exception("Doveva fallire per idDipendente <= 0");
            } catch (\InvalidArgumentException $e) {
                if (!str_contains($e->getMessage(), 'Identificativo dipendente')) {
                    throw new \Exception("Messaggio inatteso: " . $e->getMessage());
                }
            }

            try {
                $this->service->prenota(1, -5, [101]);
                throw new \Exception("Doveva fallire per idEsame <= 0");
            } catch (\InvalidArgumentException $e) {
                if (!str_contains($e->getMessage(), 'Identificativo esame')) {
                    throw new \Exception("Messaggio inatteso: " . $e->getMessage());
                }
            }

            try {
                $this->service->prenota(1, 2, [0, -1, 'abc']);
                throw new \Exception("Doveva fallire per lista id non valida");
            } catch (\InvalidArgumentException $e) {
                if (!str_contains($e->getMessage(), 'almeno un allievo valido')) {
                    throw new \Exception("Messaggio inatteso: " . $e->getMessage());
                }
            }
        });

        // TEST 13: Deduplicazione automatica degli ID allievi
        $this->test("13. Deduplicazione automatica degli allievi selezionati più volte nello stesso form", function() {
            // Prenotiamo Anna (103) per la pratica inviando l'ID duplicato [103, 103, 103]
            $pren = $this->service->prenota(1, 3, [103, 103, 103]);
            if (count($pren) !== 1) {
                throw new \Exception("Attesa esattamente 1 prenotazione dopo deduplicazione, ottenute: " . count($pren));
            }
        });

        // TEST 14: Allievo non attivo non può essere prenotato
        $this->test("14. Impossibile prenotare un allievo disattivato", function() {
            $iscrittoDisattivato = $this->creaIscritto(104, 'Giacomo', 'Neri');
            $refIsc = new \ReflectionClass(EIscritto::class);
            $propStato = $refIsc->getParentClass()->getProperty('stato');
            $propStato->setAccessible(true);
            $propStato->setValue($iscrittoDisattivato, false);
            $this->iscrittiStore[104] = $iscrittoDisattivato;
            $this->quizSvoltiPerIscritto[104] = 10;
            $this->quizSuperatiPerIscritto[104] = 10;

            try {
                $this->service->prenota(1, 2, [104]);
                throw new \Exception("Doveva lanciare eccezione per account disattivato");
            } catch (\InvalidArgumentException $e) {
                if (!str_contains($e->getMessage(), 'non ha un account attivo')) {
                    throw new \Exception("Messaggio inatteso: " . $e->getMessage());
                }
            }
        });

        // TEST 15: Validazione di conferma() per input vuoto o non valido
        $this->test("15. conferma() rifiuta array vuoti o elementi non validi", function() {
            try {
                $this->service->conferma([]);
                throw new \Exception("Doveva fallire per array vuoto");
            } catch (\InvalidArgumentException $e) {
                if (!str_contains($e->getMessage(), 'Nessuna prenotazione da confermare')) {
                    throw new \Exception("Messaggio inatteso: " . $e->getMessage());
                }
            }

            try {
                $this->service->conferma([new \stdClass()]);
                throw new \Exception("Doveva fallire per elemento non EPrenotazioneEsami");
            } catch (\InvalidArgumentException $e) {
                if (!str_contains($e->getMessage(), 'Elemento della prenotazione non valido')) {
                    throw new \Exception("Messaggio inatteso: " . $e->getMessage());
                }
            }
        });

        // TEST 16: Metodo atomico prenotaEConferma()
        $this->test("16. prenotaEConferma() esegue la prenotazione e il salvataggio atomico", function() {
            $iscrittoNuovo = $this->creaIscritto(105, 'Elena', 'Gialli');
            $this->iscrittiStore[105] = $iscrittoNuovo;
            $this->quizSvoltiPerIscritto[105] = 8;
            $this->quizSuperatiPerIscritto[105] = 8;

            $pren = $this->service->prenotaEConferma(1, 2, [105]);
            if (count($pren) !== 1) {
                throw new \Exception("Attesa 1 prenotazione confermata, trovate: " . count($pren));
            }
            if ($pren[0]->getAllievo()->getId() !== 105) {
                throw new \Exception("Allievo prenotato inatteso.");
            }
        });

        // TEST 17: Esclusione allievo con sovrapposizione oraria tra lezione ed esame
        $this->test("17. Esclusione dagli idonei per allievo che ha già una lezione prenotata nello stesso orario", function() use ($esameTeoriaFuturo) {
            $iscrittoConLezione = $this->creaIscritto(106, 'Fabio', 'Viola');
            $this->iscrittiStore[106] = $iscrittoConLezione;
            $this->quizSvoltiPerIscritto[106] = 9;
            $this->quizSuperatiPerIscritto[106] = 9;

            // Registriamo una lezione prenotata per Fabio esattamente nell'orario dell'esame 2
            $orarioEsame2 = $esameTeoriaFuturo->getDataEs()->format('Y-m-d H:i');
            $this->lezioniPrenotateStore[106] = [$orarioEsame2];

            $idonei = $this->service->getIscrittiIdonei(2);
            $ids = array_map(fn($i) => $i->getId(), $idonei);
            if (in_array(106, $ids)) {
                throw new \Exception("Fabio Viola (106) non deve figurare tra gli idonei perché ha già una lezione in quell'orario.");
            }
        });

        // TEST 18: Blocco prenotazione esame se l'allievo ha una lezione nello stesso orario
        $this->test("18. Blocco prenotazione esame per allievo che ha già una lezione nello stesso orario", function() {
            try {
                $this->service->prenota(1, 2, [106]);
                throw new \Exception("Doveva lanciare eccezione per lezione nello stesso orario.");
            } catch (\InvalidArgumentException $e) {
                if (!str_contains($e->getMessage(), 'ha già una lezione prenotata nello stesso orario')) {
                    throw new \Exception("Messaggio inatteso: " . $e->getMessage());
                }
            }
        });

        // TEST 19: Allievo eliminato/inesistente al momento della prenotazione POST
        $this->test("19. Blocco prenotazione se l'allievo viene eliminato prima del submit POST", function() {
            try {
                $this->service->prenota(1, 2, [9999]);
                throw new \Exception("Doveva lanciare eccezione per allievo inesistente.");
            } catch (\InvalidArgumentException $e) {
                if (!str_contains($e->getMessage(), 'Allievo con ID 9999 non trovato')) {
                    throw new \Exception("Messaggio inatteso: " . $e->getMessage());
                }
            }
        });

        // TEST 20: Esclusione allievo disattivato sia in getIscrittiIdonei sia in prenota
        $this->test("20. Allievo disattivato (stato=false) non compare negli idonei e viene rifiutato in prenota", function() {
            $idonei = $this->service->getIscrittiIdonei(2);
            $ids = array_map(fn($i) => $i->getId(), $idonei);
            if (in_array(104, $ids)) {
                throw new \Exception("L'allievo 104 (disattivato) non deve figurare tra gli idonei.");
            }

            try {
                $this->service->prenota(1, 2, [104]);
                throw new \Exception("Doveva lanciare eccezione per allievo disattivato.");
            } catch (\InvalidArgumentException $e) {
                if (!str_contains($e->getMessage(), 'non ha un account attivo')) {
                    throw new \Exception("Messaggio inatteso: " . $e->getMessage());
                }
            }
        });

        // TEST 21: Sessione d'esame inesistente o cancellata
        $this->test("21. Blocco recupero idonei e prenotazione su sessione d'esame inesistente o cancellata", function() {
            try {
                $this->service->getIscrittiIdonei(9999);
                throw new \Exception("Doveva lanciare eccezione per esame inesistente in getIscrittiIdonei.");
            } catch (\InvalidArgumentException $e) {
                if (!str_contains($e->getMessage(), 'Sessione d\'esame non trovata')) {
                    throw new \Exception("Messaggio inatteso: " . $e->getMessage());
                }
            }

            try {
                $this->service->prenota(1, 9999, [101]);
                throw new \Exception("Doveva lanciare eccezione per esame inesistente in prenota.");
            } catch (\InvalidArgumentException $e) {
                if (!str_contains($e->getMessage(), 'Sessione d\'esame non trovata')) {
                    throw new \Exception("Messaggio inatteso: " . $e->getMessage());
                }
            }
        });

        // TEST 22: Sessione d'esame già trascorsa o iniziata
        $this->test("22. Blocco recupero idonei e prenotazione su sessione d'esame passata o già iniziata", function() {
            try {
                $this->service->getIscrittiIdonei(1); // Esame 1 è nel passato (-10 days)
                throw new \Exception("Doveva lanciare eccezione per esame passato in getIscrittiIdonei.");
            } catch (\InvalidArgumentException $e) {
                if (!str_contains($e->getMessage(), 'già iniziata o trascorsa')) {
                    throw new \Exception("Messaggio inatteso: " . $e->getMessage());
                }
            }

            try {
                $this->service->prenota(1, 1, [101]);
                throw new \Exception("Doveva lanciare eccezione per esame passato in prenota.");
            } catch (\InvalidArgumentException $e) {
                if (!str_contains($e->getMessage(), 'già iniziata o trascorsa')) {
                    throw new \Exception("Messaggio inatteso: " . $e->getMessage());
                }
            }
        });

        // TEST 23: Esclusione allievo che ha già un'altra sessione d'esame futura prenotata per la stessa tipologia
        $this->test("23. Esclusione e blocco prenotazione per allievo già prenotato ad altra sessione futura della stessa tipologia", function() {
            // Creiamo un secondo esame di teoria futuro (id=4)
            $esameTeoriaFuturo2 = new EEsame();
            $esameTeoriaFuturo2->setIdEsame(4);
            $esameTeoriaFuturo2->init(TipologiaEsame::TEORIA, new DateTime('+12 days 10:00'));
            $this->esamiStore[4] = $esameTeoriaFuturo2;

            $iscrittoUnico = $this->creaIscritto(107, 'Giorgio', 'Singolo');
            $this->iscrittiStore[107] = $iscrittoUnico;
            $this->quizSvoltiPerIscritto[107] = 8;
            $this->quizSuperatiPerIscritto[107] = 8;

            // Prenotiamo Giorgio alla prima sessione di teoria (id=2)
            $pren1 = $this->service->prenotaEConferma(1, 2, [107]);
            $this->prenotazioniStore[1070] = $pren1[0];

            // Verifichiamo che per la seconda sessione di teoria (id=4), Giorgio venga escluso dagli idonei
            $idonei = $this->service->getIscrittiIdonei(4);
            $ids = array_map(fn($i) => $i->getId(), $idonei);
            if (in_array(107, $ids)) {
                throw new \Exception("Giorgio non deve figurare tra gli idonei per un'altra sessione di teoria.");
            }

            // Verifichiamo che tentando di forzare la prenotazione alla seconda sessione di teoria (id=4) venga lanciata eccezione
            try {
                $this->service->prenota(1, 4, [107]);
                throw new \Exception("Doveva lanciare eccezione per sessione teoria futura già attiva.");
            } catch (\InvalidArgumentException $e) {
                if (!str_contains($e->getMessage(), 'ha già una sessione d\'esame di teoria futura prenotata')) {
                    throw new \Exception("Messaggio inatteso: " . $e->getMessage());
                }
            }
        });

        // TEST 24: senza quiz disponibili non è possibile raggiungere la soglia minima di copertura
        $this->test("24. Nessun allievo è idoneo quando il catalogo dei quiz è vuoto", function() {
            // Settiamo totale quiz DB a 0
            $this->totaleQuizDisponibili = 0;

            $iscrittoAttivo = $this->creaIscritto(108, 'Paolo', 'Simulatore');
            $this->iscrittiStore[108] = $iscrittoAttivo;
            // Paolo ha svolto 5 simulazioni e ne ha superate 4 (80% >= 70%)
            $this->quizSvoltiPerIscritto[108] = 5;
            $this->quizSuperatiPerIscritto[108] = 4;

            // Sessione di teoria futuro 2
            $idonei = $this->service->getIscrittiIdonei(2);
            $ids = array_map(fn($i) => $i->getId(), $idonei);
            if (in_array(108, $ids)) {
                throw new \Exception("Paolo (108) non deve risultare idoneo quando non esistono quiz disponibili.");
            }

            // Allievo che con contaQuiz=0 non ha svolto alcuna simulazione (0 svolti)
            $iscrittoZeroSvolti = $this->creaIscritto(109, 'Claudio', 'Pigro');
            $this->iscrittiStore[109] = $iscrittoZeroSvolti;
            $this->quizSvoltiPerIscritto[109] = 0;
            $this->quizSuperatiPerIscritto[109] = 0;

            $idonei2 = $this->service->getIscrittiIdonei(2);
            $ids2 = array_map(fn($i) => $i->getId(), $idonei2);
            if (in_array(109, $ids2)) {
                throw new \Exception("Claudio (109) non deve figurare tra gli idonei non avendo svolto alcuna simulazione.");
            }

            // Ripristiniamo il valore di test di default
            $this->totaleQuizDisponibili = 10;
        });

        $this->test("25. Il POST rifiuta un allievo non idoneo anche per l'esame teorico", function() {
            try {
                $this->service->prenota(1, 2, [102]);
                throw new \Exception("Doveva lanciare un'eccezione per idoneità insufficiente.");
            } catch (\InvalidArgumentException $e) {
                if (!str_contains($e->getMessage(), 'non possiede i requisiti di idoneità')) {
                    throw new \Exception("Messaggio inatteso: " . $e->getMessage());
                }
            }
        });

        $this->test("26. Il profilo quiz espone percentuali e idoneità coerenti", function() {
            $reflection = new \ReflectionClass(SPrenotazioneEsame::class);
            $factory = $reflection->getMethod('creaIdoneitaService');
            $factory->setAccessible(true);
            $idoneitaService = $factory->invoke($this->service);

            $profiloMario = null;
            foreach ($idoneitaService->getProfiliQuizIscritti() as $profilo) {
                if ($profilo->getIscritto()->getId() === 101) {
                    $profiloMario = $profilo;
                    break;
                }
            }

            if ($profiloMario === null ||
                abs($profiloMario->getPercentualeQuizSvolti() - 80.0) > 0.001 ||
                abs($profiloMario->getPercentualeQuizSuperati() - 100.0) > 0.001 ||
                !$profiloMario->isIdoneo()) {
                throw new \Exception('Profilo didattico di Mario non coerente con 8 quiz svolti e superati su 10.');
            }
        });

        echo "\n====================================================================\n";
        echo " RISULTATO: {$this->passati}/{$this->totali} test passati con successo!\n";
        echo "====================================================================\n";
    }
}

$testRunner = new TestCasiLimiteEsami();
$testRunner->runAll();
