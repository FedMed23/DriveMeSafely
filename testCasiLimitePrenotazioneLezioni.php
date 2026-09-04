<?php

namespace CamassoMedelago\DriveMeSafely;

require_once __DIR__ . '/vendor/autoload.php';

use CamassoMedelago\DriveMeSafely\Entity\EAula;
use CamassoMedelago\DriveMeSafely\Entity\EArgomentoMinisteriale;
use CamassoMedelago\DriveMeSafely\Entity\ELezione;
use CamassoMedelago\DriveMeSafely\Entity\ELezionePratica;
use CamassoMedelago\DriveMeSafely\Entity\ELezioneTeoria;
use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Entity\EPatente;
use CamassoMedelago\DriveMeSafely\Entity\EPrenotazioneLezione;
use CamassoMedelago\DriveMeSafely\Entity\StatoPrenotazione;
use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FLezione;
use CamassoMedelago\DriveMeSafely\Foundation\FPrenotazioneEsami;
use CamassoMedelago\DriveMeSafely\Foundation\FPrenotazioneLezione;
use CamassoMedelago\DriveMeSafely\Service\SPrenotazioneLezione;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class TestPrenotazioneLezioneCasiLimite
{
    private SPrenotazioneLezione $service;
    public array $iscrittiStore = [];
    public array $lezioniStore = [];
    public array $prenotazioniStore = [];
    public array $esamiPrenotatiStore = []; // [idIscritto => [dataOraString]]
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
                    public function isTransactionActive(): bool { return false; }
                };
            }
            public function getCache() { return null; }
            public function getUnitOfWork() { return null; }
            public function getConfiguration() { return null; }
            public function isOpen() { return true; }
            public function getEventManager() { return null; }
            public function getExpressionBuilder() { return null; }
            public function lock($entity, $lockMode, $lockVersion = null) {}
            public function getHydrator($hydrationMode) { return null; }
            public function newHydrator($hydrationMode) { return null; }
            public function getProxyFactory() { return null; }
            public function getFilters() { return null; }
            public function isFiltersStateClean() { return true; }
            public function hasFilters() { return false; }
            public function createQuery($dql = '') { return null; }
            public function createNamedQuery($name) { return null; }
            public function createNativeQuery($sql, \Doctrine\ORM\Query\ResultSetMapping $rsm) { return null; }
            public function createNamedNativeQuery($name) { return null; }
            public function createQueryBuilder() { return null; }
            public function getReference($entityName, $id) { return null; }
            public function getPartialReference($entityName, $id) { return null; }
            public function transactional($func) { return $func($this); }
            public function wrapInTransaction(callable $func): mixed { return $func($this); }
        };

        $this->service = new SPrenotazioneLezione($emMock);
        $reflection = new \ReflectionClass($this->service);

        // Mock FIscritto
        $fIscrittoMock = new class($test, $emMock) extends FIscritto {
            private TestPrenotazioneLezioneCasiLimite $test;
            public function __construct(TestPrenotazioneLezioneCasiLimite $test, EntityManagerInterface $em) {
                parent::__construct($em);
                $this->test = $test;
            }
            public function findById(int $id): ?EIscritto {
                return $this->test->iscrittiStore[$id] ?? null;
            }
        };

        // Mock FLezione
        $fLezioneMock = new class($test, $emMock) extends FLezione {
            private TestPrenotazioneLezioneCasiLimite $test;
            public function __construct(TestPrenotazioneLezioneCasiLimite $test, EntityManagerInterface $em) {
                parent::__construct($em);
                $this->test = $test;
            }
            public function findById(int $idLezione): ?ELezione {
                return $this->test->lezioniStore[$idLezione] ?? null;
            }
            public function findByIdForUpdate(int $idLezione): ?ELezione {
                return $this->findById($idLezione);
            }
        };

        // Mock FPrenotazioneLezione
        $fPrenotazioneMock = new class($test, $emMock) extends FPrenotazioneLezione {
            private TestPrenotazioneLezioneCasiLimite $test;
            public function __construct(TestPrenotazioneLezioneCasiLimite $test, EntityManagerInterface $em) {
                parent::__construct($em);
                $this->test = $test;
            }
            public function findById(int $id): ?EPrenotazioneLezione {
                return $this->test->prenotazioniStore[$id] ?? null;
            }
            public function findByIscrittoAndLezione(int $idIscritto, int $idLezione): ?EPrenotazioneLezione {
                foreach ($this->test->prenotazioniStore as $p) {
                    /** @var EPrenotazioneLezione $p */
                    if ($p->getIscritto()->getId() === $idIscritto && $p->getLezione()->getIdLezione() === $idLezione) {
                        return $p;
                    }
                }
                return null;
            }
            public function findByIscrittoId(int $idIscritto): array {
                $res = [];
                foreach ($this->test->prenotazioniStore as $p) {
                    if ($p->getIscritto()->getId() === $idIscritto) {
                        $res[] = $p;
                    }
                }
                return $res;
            }
            public function findLezioniDisponibili(int $idIscritto): array {
                $prenotateDallIscritto = [];
                foreach ($this->test->prenotazioniStore as $p) {
                    if ($p->getIscritto()->getId() === $idIscritto && $p->getStato() === StatoPrenotazione::PRENOTATA) {
                        $prenotateDallIscritto[$p->getLezione()->getIdLezione()] = true;
                    }
                }
                $guidePrenotateDaAltri = [];
                foreach ($this->test->prenotazioniStore as $p) {
                    if ($p->getStato() === StatoPrenotazione::PRENOTATA && $p->getLezione() instanceof ELezionePratica) {
                        $guidePrenotateDaAltri[$p->getLezione()->getIdLezione()] = true;
                    }
                }

                $disponibili = [];
                $ora = new DateTimeImmutable();
                foreach ($this->test->lezioniStore as $l) {
                    /** @var ELezione $l */
                    if ($l->getDataOra() <= $ora) continue;
                    if (isset($prenotateDallIscritto[$l->getIdLezione()])) continue;
                    if ($l instanceof ELezionePratica && isset($guidePrenotateDaAltri[$l->getIdLezione()])) continue;
                    if ($l instanceof ELezioneTeoria) {
                        $capienza = $l->getAula()?->getCapienzaMassima() ?? 30;
                        if ($this->isAulaPiena($l->getIdLezione(), $capienza)) continue;
                    }
                    $disponibili[] = $l;
                }
                return $disponibili;
            }
            public function isGuidaPraticaPrenotata(int $idLezione): bool {
                foreach ($this->test->prenotazioniStore as $p) {
                    if ($p->getLezione()->getIdLezione() === $idLezione && $p->getStato() === StatoPrenotazione::PRENOTATA) {
                        return true;
                    }
                }
                return false;
            }
            public function isAulaPiena(int $idLezione, int $capienzaMassima): bool {
                $count = 0;
                foreach ($this->test->prenotazioniStore as $p) {
                    if ($p->getLezione()->getIdLezione() === $idLezione && $p->getStato() === StatoPrenotazione::PRENOTATA) {
                        $count++;
                    }
                }
                return $count >= $capienzaMassima;
            }
            public function haLezioneInOrario(int $idIscritto, DateTimeImmutable $dataOra): bool {
                foreach ($this->test->prenotazioniStore as $p) {
                    if ($p->getIscritto()->getId() === $idIscritto && $p->getStato() === StatoPrenotazione::PRENOTATA) {
                        if ($p->getLezione()->getDataOra()->format('Y-m-d H:i') === $dataOra->format('Y-m-d H:i')) {
                            return true;
                        }
                    }
                }
                return false;
            }
            public function save(EPrenotazioneLezione $prenotazione): void {
                if ($prenotazione->getIdPrenotazione() === null) {
                    $id = count($this->test->prenotazioniStore) + 1;
                    $prenotazione->setIdPrenotazione($id);
                }
                $this->test->prenotazioniStore[$prenotazione->getIdPrenotazione()] = $prenotazione;
            }
        };

        // Mock FPrenotazioneEsami
        $fPrenotazioneEsamiMock = new class($test, $emMock) extends FPrenotazioneEsami {
            private TestPrenotazioneLezioneCasiLimite $test;
            public function __construct(TestPrenotazioneLezioneCasiLimite $test, EntityManagerInterface $em) {
                parent::__construct($em);
                $this->test = $test;
            }
            public function haEsameInOrario(int $idIscritto, DateTimeImmutable $dataOra): bool {
                $esami = $this->test->esamiPrenotatiStore[$idIscritto] ?? [];
                return in_array($dataOra->format('Y-m-d H:i'), $esami, true);
            }
        };

        $propFIscritto = $reflection->getProperty('fIscritto');
        $propFIscritto->setAccessible(true);
        $propFIscritto->setValue($this->service, $fIscrittoMock);

        $propFLezione = $reflection->getProperty('fLezione');
        $propFLezione->setAccessible(true);
        $propFLezione->setValue($this->service, $fLezioneMock);

        $propFPren = $reflection->getProperty('fPrenotazione');
        $propFPren->setAccessible(true);
        $propFPren->setValue($this->service, $fPrenotazioneMock);

        $propFPrenEsami = $reflection->getProperty('fPrenotazioneEsami');
        $propFPrenEsami->setAccessible(true);
        $propFPrenEsami->setValue($this->service, $fPrenotazioneEsamiMock);
    }

    private function creaAllievo(int $id, string $nome = "Mario", string $cognome = "Rossi"): EIscritto
    {
        $patente = new EPatente('B', 'Patente B');
        $patente->setId(1);

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

        $this->iscrittiStore[$id] = $iscritto;
        return $iscritto;
    }

    private function assert(string $nomeTest, bool $condizione, string $dettaglio = ''): void
    {
        $this->totali++;
        if ($condizione) {
            $this->passati++;
            echo "  [PASS] {$nomeTest}\n";
        } else {
            echo "  [FAIL] {$nomeTest} - {$dettaglio}\n";
        }
    }

    public function runAll(): void
    {
        echo "====================================================\n";
        echo "   TEST SUITE: FLUSSO PRENOTAZIONE & ANNULLAMENTO   \n";
        echo "====================================================\n\n";

        $this->testPrenotazioneGuidaPraticaValida();
        $this->testEsclusioneGuidaGiaPrenotataDalPalinsesto();
        $this->testIdempotenzaRiPrenotazioneDopoAnnullamento();
        $this->testProtezioneAntiIDORAnnullamento();
        $this->testPreavvisoMinimoAnnullamento();
        $this->testBloccoAnnullamentoLezioniPassate();
        $this->testBloccoPrenotazioneContemporaneaStessoOrario();
        $this->testValidazioneParametriPrenotazione();
        $this->testIncongruenzaTipoLezioneRichiesto();
        $this->testEsclusioneLezioneTeoriaAulaPienaDalPalinsesto();
        $this->testBloccoPrenotazioneLezioneConEsameContemporaneo();

        echo "\n----------------------------------------------------\n";
        echo "RISULTATO FINALE: {$this->passati} / {$this->totali} test superati con successo.\n";
        echo "====================================================\n";
    }

    private function testEsclusioneLezioneTeoriaAulaPienaDalPalinsesto(): void
    {
        echo "\n10) Test Esclusione Lezione Teoria con Aula Satura dal Palinsesto:\n";
        $data = (new DateTimeImmutable())->modify('+8 days')->setTime(10, 0);
        $teoriaPiena = new ELezioneTeoria($data, EAula::AULA_B, EArgomentoMinisteriale::SEGNALETICA); // Capienza 25
        $teoriaPiena->setIdLezione(80);
        $this->lezioniStore[80] = $teoriaPiena;

        // Riempiamo l'aula con 25 prenotazioni attive
        for ($i = 100; $i < 125; $i++) {
            $studente = $this->creaAllievo($i, "Studente{$i}", "Rossi");
            $pren = EPrenotazioneLezione::crea($studente, $teoriaPiena, StatoPrenotazione::PRENOTATA);
            $this->prenotazioniStore[] = $pren;
        }

        $allievoRichiedente = $this->creaAllievo(999, 'Nuovo', 'Richiedente');
        $calendario = $this->service->getCalendarioAllievo(999);
        $disponibili = $calendario['lezioniDisponibili'];

        $trovata = false;
        foreach ($disponibili as $d) {
            if ($d->getIdLezione() === 80) $trovata = true;
        }
        $this->assert("La lezione di teoria in Aula B con 25 iscritti (satura) non compare tra le disponibili", !$trovata);
    }

    private function testValidazioneParametriPrenotazione(): void
    {
        echo "\n8) Test Validazione Parametri Prenotazione (ID non validi):\n";
        $bloccatoIdIscritto = false;
        try {
            $this->service->prenotaLezione(0, 10);
        } catch (\InvalidArgumentException $e) {
            $bloccatoIdIscritto = true;
        }
        $this->assert("ID iscritto <= 0 rifiutato con eccezione", $bloccatoIdIscritto);

        $bloccatoIdLezione = false;
        try {
            $this->service->prenotaLezione(1, -5);
        } catch (\InvalidArgumentException $e) {
            $bloccatoIdLezione = true;
        }
        $this->assert("ID lezione <= 0 rifiutato con eccezione", $bloccatoIdLezione);
    }

    private function testIncongruenzaTipoLezioneRichiesto(): void
    {
        echo "\n9) Test Incongruenza Tipologia Lezione Selezionata:\n";
        $allievo = $this->creaAllievo(9, 'Marco', 'Mismatch');
        $data = (new DateTimeImmutable())->modify('+7 days')->setTime(14, 0);
        $teoria = new ELezioneTeoria($data, EAula::AULA_A, EArgomentoMinisteriale::PRECEDENZE);
        $teoria->setIdLezione(70);
        $this->lezioniStore[70] = $teoria;

        $bloccatoMismatch = false;
        try {
            // L'allievo invia form Guida Pratica per una lezione che in realtà è di Teoria
            $this->service->prenotaLezione(9, 70, 'PRATICA');
        } catch (\InvalidArgumentException $e) {
            $bloccatoMismatch = true;
        }
        $this->assert("Tentativo di prenotare una teoria tramite form pratica viene bloccato", $bloccatoMismatch);
    }

    private function testPrenotazioneGuidaPraticaValida(): void
    {
        echo "1) Test Prenotazione Guida Pratica Valida:\n";
        $allievo = $this->creaAllievo(1, 'Allievo', 'Uno');
        $data = (new DateTimeImmutable())->modify('+3 days')->setTime(10, 0);
        $guida = new ELezionePratica($data, 'Istruttore Top', 'AA111AA');
        $guida->setIdLezione(10);
        $this->lezioniStore[10] = $guida;

        $prenotazione = $this->service->prenotaLezione(1, 10);
        $this->assert("Prenotazione registrata con successo in stato PRENOTATA", $prenotazione->getStato() === StatoPrenotazione::PRENOTATA);
        $this->assert("Prenotazione associata correttamente all'allievo 1", $prenotazione->getIscritto()->getId() === 1);
    }

    private function testEsclusioneGuidaGiaPrenotataDalPalinsesto(): void
    {
        echo "\n2) Test Esclusione Guida Già Prenotata dalle Disponibili per altri:\n";
        $allievo2 = $this->creaAllievo(2, 'Allievo', 'Due');
        $calendario = $this->service->getCalendarioAllievo(2);
        $disponibili = $calendario['lezioniDisponibili'];

        $trovata = false;
        foreach ($disponibili as $d) {
            if ($d->getIdLezione() === 10) $trovata = true;
        }
        $this->assert("La guida pratica 10 già prenotata non compare tra le disponibili dell'allievo 2", !$trovata);
    }

    private function testIdempotenzaRiPrenotazioneDopoAnnullamento(): void
    {
        echo "\n3) Test Idempotenza Ri-prenotazione dopo Annullamento:\n";
        $allievo = $this->creaAllievo(3, 'Allievo', 'Tre');
        $data = (new DateTimeImmutable())->modify('+4 days')->setTime(11, 0);
        $teoria = new ELezioneTeoria($data, EAula::AULA_A, EArgomentoMinisteriale::SEGNALETICA);
        $teoria->setIdLezione(20);
        $this->lezioniStore[20] = $teoria;

        // Prima prenotazione
        $p1 = $this->service->prenotaLezione(3, 20);
        $idPrenotazione = $p1->getIdPrenotazione();

        // Annullamento con preavviso valido
        $this->service->annullaPrenotazione($idPrenotazione, 3);
        $this->assert("Prenotazione passata in stato ANNULLATA", $p1->getStato() === StatoPrenotazione::ANNULLATA);

        // Ri-prenotazione della stessa lezione: non deve duplicare il record ma riattivare p1
        $p2 = $this->service->prenotaLezione(3, 20);
        $this->assert("La ri-prenotazione riattiva lo stesso record ID senza duplicati", $p2->getIdPrenotazione() === $idPrenotazione && $p2->getStato() === StatoPrenotazione::PRENOTATA);
    }

    private function testProtezioneAntiIDORAnnullamento(): void
    {
        echo "\n4) Test Sicurezza Anti-IDOR su Annullamento:\n";
        $allievoA = $this->creaAllievo(4, 'Vittima', 'Rossi');
        $allievoAttaccante = $this->creaAllievo(5, 'Attaccante', 'Hacker');
        $data = (new DateTimeImmutable())->modify('+5 days')->setTime(15, 0);
        $guida = new ELezionePratica($data, 'Istruttore X', 'XX000XX');
        $guida->setIdLezione(30);
        $this->lezioniStore[30] = $guida;

        $prenotazioneVittima = $this->service->prenotaLezione(4, 30);
        $idVictimPren = $prenotazioneVittima->getIdPrenotazione();

        $bloccato = false;
        try {
            // L'utente 5 prova ad annullare la prenotazione dell'utente 4
            $this->service->annullaPrenotazione($idVictimPren, 5);
        } catch (\InvalidArgumentException $e) {
            $bloccato = true;
        }
        $this->assert("Tentativo di annullamento prenotazione altrui bloccato con eccezione di sicurezza", $bloccato);
        $this->assert("La prenotazione della vittima è rimasta attiva (PRENOTATA)", $prenotazioneVittima->getStato() === StatoPrenotazione::PRENOTATA);
    }

    private function testPreavvisoMinimoAnnullamento(): void
    {
        echo "\n5) Test Preavviso Minimo per Annullamento (Meno di 2 ore):\n";
        $allievo = $this->creaAllievo(6, 'Mario', 'Ritardatario');
        // Lezione fissata tra 1 ora (preavviso insufficiente)
        $dataImminente = (new DateTimeImmutable())->modify('+1 hour');
        $lezioneImminente = new ELezionePratica($dataImminente, 'Istruttore Flash', 'FF111FF');
        $lezioneImminente->setIdLezione(40);
        $this->lezioniStore[40] = $lezioneImminente;

        $pren = $this->service->prenotaLezione(6, 40);

        $bloccato = false;
        try {
            $this->service->annullaPrenotazione($pren->getIdPrenotazione(), 6);
        } catch (\InvalidArgumentException $e) {
            $bloccato = true;
        }
        $this->assert("Annullamento a meno di 2 ore dall'inizio della lezione viene bloccato", $bloccato);
    }

    private function testBloccoAnnullamentoLezioniPassate(): void
    {
        echo "\n6) Test Blocco Annullamento per Lezioni Già Passate:\n";
        $allievo = $this->creaAllievo(7, 'Carlo', 'Passato');
        $dataPassata = (new DateTimeImmutable())->modify('-1 day');
        $lezionePassata = new ELezionePratica($dataPassata, 'Istruttore Passato', 'PP222PP');
        $lezionePassata->setIdLezione(50);
        $this->lezioniStore[50] = $lezionePassata;

        $prenPassata = EPrenotazioneLezione::crea($allievo, $lezionePassata, StatoPrenotazione::PRENOTATA);
        $this->service->conferma($prenPassata);

        $bloccato = false;
        try {
            $this->service->annullaPrenotazione($prenPassata->getIdPrenotazione(), 7);
        } catch (\InvalidArgumentException) {
            $bloccato = true;
        }
        $this->assert("Annullamento per lezione già passata o svolta bloccato", $bloccato);
    }

    private function testBloccoPrenotazioneContemporaneaStessoOrario(): void
    {
        echo "\n7) Test Blocco Prenotazioni Sovrapposte nello Stesso Orario per lo Stesso Allievo:\n";
        $allievo = $this->creaAllievo(8, 'Luca', 'Doppio');
        $data = (new DateTimeImmutable())->modify('+6 days')->setTime(16, 0);

        $teoria1 = new ELezioneTeoria($data, EAula::AULA_A, EArgomentoMinisteriale::VELOCITA);
        $teoria1->setIdLezione(61);
        $this->lezioniStore[61] = $teoria1;

        $teoria2 = new ELezioneTeoria($data, EAula::AULA_B, EArgomentoMinisteriale::MECCANICA);
        $teoria2->setIdLezione(62);
        $this->lezioniStore[62] = $teoria2;

        $this->service->prenotaLezione(8, 61);

        $bloccato = false;
        try {
            $this->service->prenotaLezione(8, 62);
        } catch (\InvalidArgumentException) {
            $bloccato = true;
        }
        $this->assert("Allievo non può prenotare due lezioni nello stesso orario contemporaneo", $bloccato);
    }

    private function testBloccoPrenotazioneLezioneConEsameContemporaneo(): void
    {
        echo "\n11) Test Blocco Prenotazione Lezione se l'Allievo ha già una Sessione d'Esame nello Stesso Orario:\n";
        $allievo = $this->creaAllievo(10, 'Sara', 'EsameConflict');
        $data = (new DateTimeImmutable())->modify('+9 days')->setTime(10, 0);

        $teoria = new ELezioneTeoria($data, EAula::AULA_A, EArgomentoMinisteriale::SEGNALETICA);
        $teoria->setIdLezione(90);
        $this->lezioniStore[90] = $teoria;

        // Registriamo un esame per Sara alla stessa data/ora
        $this->esamiPrenotatiStore[10] = [$data->format('Y-m-d H:i')];

        $bloccato = false;
        try {
            $this->service->prenotaLezione(10, 90);
        } catch (\InvalidArgumentException $e) {
            if (str_contains($e->getMessage(), 'sessione d\'esame prenotata')) {
                $bloccato = true;
            }
        }
        $this->assert("Prenotazione lezione bloccata per concomitanza con sessione d'esame", $bloccato);
    }
}

$runner = new TestPrenotazioneLezioneCasiLimite();
$runner->runAll();
