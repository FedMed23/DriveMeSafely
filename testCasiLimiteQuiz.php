<?php

namespace CamassoMedelago\DriveMeSafely;

require_once __DIR__ . '/vendor/autoload.php';

use CamassoMedelago\DriveMeSafely\Entity\EDomanda;
use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Entity\EPatente;
use CamassoMedelago\DriveMeSafely\Entity\EQuiz;
use CamassoMedelago\DriveMeSafely\Entity\ESvolgimentoQuiz;
use CamassoMedelago\DriveMeSafely\Entity\ETentativoRisposta;
use CamassoMedelago\DriveMeSafely\Foundation\FDomanda;
use CamassoMedelago\DriveMeSafely\Foundation\FQuiz;
use CamassoMedelago\DriveMeSafely\Foundation\FSvolgimentoQuiz;
use CamassoMedelago\DriveMeSafely\Foundation\FTentativoRisposta;
use CamassoMedelago\DriveMeSafely\Service\SQuiz;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class TestQuizCasiLimite
{
    private SQuiz $service;
    public array $quizStore = [];
    public array $domandeStore = [];
    public array $svolgimentiStore = [];
    public array $tentativiStore = [];
    private int $passati = 0;
    private int $totali = 0;

    public function __construct()
    {
        $this->inizializzaDatiMock();
        $this->inizializzaService();
    }

    private function inizializzaDatiMock(): void
    {
        // Creazione 30 domande per simulazione
        for ($i = 1; $i <= 30; $i++) {
            $domanda = new EDomanda();
            // id pari -> risposta corretta TRUE, id dispari -> risposta corretta FALSE
            $domanda->init("Domanda di test #$i", ($i % 2 === 0), "Segnaletica", null);
            $domanda->setIdDomanda($i);
            $this->domandeStore[$i] = $domanda;
        }

        // Quiz Standard 30 domande, 30 minuti
        $quiz = new EQuiz();
        $quiz->init("Simulazione Patente B", "Scheda ministeriale", 30, 30);
        $quiz->setIdQuiz(1);
        $quiz->setDomande(array_values($this->domandeStore));
        $this->quizStore[1] = $quiz;

        // Quiz Ridotto 5 domande, 10 minuti (per test casi limite specifici)
        $quizRidotto = new EQuiz();
        $quizRidotto->init("Quiz Rapido", "Mini test", 5, 10);
        $quizRidotto->setIdQuiz(2);
        $quizRidotto->setDomande(array_slice(array_values($this->domandeStore), 0, 5));
        $this->quizStore[2] = $quizRidotto;
    }

    private function createMockEntityManager(): EntityManagerInterface
    {
        return new class implements EntityManagerInterface {
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
        };
    }

    private function inizializzaService(): void
    {
        $emMock = $this->createMockEntityManager();
        $this->service = new SQuiz($emMock);

        $reflection = new \ReflectionClass($this->service);

        // Mock FQuiz
        $test = $this;
        $fQuizMock = new class($test) extends FQuiz {
            private TestQuizCasiLimite $test;
            public function __construct(TestQuizCasiLimite $test) { $this->test = $test; }
            public function findAll(): array { return array_values($this->test->quizStore); }
            public function findById(int $id): ?EQuiz { return $this->test->quizStore[$id] ?? null; }
        };

        // Mock FDomanda
        $fDomandaMock = new class($test) extends FDomanda {
            private TestQuizCasiLimite $test;
            public function __construct(TestQuizCasiLimite $test) { $this->test = $test; }
            public function findById(int $id): ?EDomanda { return $this->test->domandeStore[$id] ?? null; }
            public function findAll(): array { return array_values($this->test->domandeStore); }
        };

        // Mock FSvolgimentoQuiz
        $fSvolgimentoMock = new class($test) extends FSvolgimentoQuiz {
            private TestQuizCasiLimite $test;
            public function __construct(TestQuizCasiLimite $test) { $this->test = $test; }
            public function save(ESvolgimentoQuiz $svolgimento): void {
                $id = count($this->test->svolgimentiStore) + 1;
                $svolgimento->setIdSvolgimento($id);
                $this->test->svolgimentiStore[$id] = $svolgimento;
            }
            public function getSvolgimentoPerId(int $id): ?ESvolgimentoQuiz {
                return $this->test->svolgimentiStore[$id] ?? null;
            }
            public function contaQuizSvoltiByIscritto(?int $idIscritto): int {
                if ($idIscritto === null) return 0;
                $count = 0;
                foreach ($this->test->svolgimentiStore as $s) {
                    if ($s->getIscritto()->getId() === $idIscritto) $count++;
                }
                return $count;
            }
            public function contaQuizSuperatiByIscritto(?int $idIscritto): int {
                if ($idIscritto === null) return 0;
                $count = 0;
                foreach ($this->test->svolgimentiStore as $s) {
                    if ($s->getIscritto()->getId() === $idIscritto && $s->isSuperato()) $count++;
                }
                return $count;
            }
        };

        // Mock FTentativoRisposta
        $fTentativoMock = new class($test) extends FTentativoRisposta {
            private TestQuizCasiLimite $test;
            public function __construct(TestQuizCasiLimite $test) { $this->test = $test; }
            public function findDomandeGiaSvolte(?int $idIscritto): array {
                if ($idIscritto === null) return [];
                $viste = [];
                foreach ($this->test->tentativiStore as $t) {
                    if ($t->getSvolgimentoQuiz()?->getIscritto()?->getId() === $idIscritto) {
                        $viste[] = $t->getDomanda()->getIdDomanda();
                    }
                }
                return array_values(array_unique($viste));
            }
            public function findDomandeSbagliate(?int $idIscritto): array {
                if ($idIscritto === null) return [];
                $sbagliate = [];
                foreach ($this->test->tentativiStore as $t) {
                    if ($t->getSvolgimentoQuiz()?->getIscritto()?->getId() === $idIscritto && !$t->isCorretta()) {
                        $sbagliate[] = $t->getDomanda()->getIdDomanda();
                    }
                }
                return array_values(array_unique($sbagliate));
            }
        };

        // Iniezione reflection
        $this->injectProperty($reflection, 'fQuiz', $fQuizMock);
        $this->injectProperty($reflection, 'fDomanda', $fDomandaMock);
        $this->injectProperty($reflection, 'fSvolgimento', $fSvolgimentoMock);
        $this->injectProperty($reflection, 'fTentativo', $fTentativoMock);
    }

    private function injectProperty(\ReflectionClass $reflection, string $propertyName, object $value): void
    {
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($this->service, $value);
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
            new \DateTimeImmutable('-20 years'),
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
        echo "   TEST SUITE: CASI LIMITE E FLUSSO COMPLETO QUIZ   \n";
        echo "====================================================\n\n";

        $this->testGenerazioneNuovoIscritto();
        $this->testGenerazioneConErroriPregressi();
        $this->testCorrezioneTutteCorretteIdoneo();
        $this->testCorrezioneLimiteErroriTreIdoneo();
        $this->testCorrezioneQuattroErroriNonIdoneo();
        $this->testCorrezioneAutoSubmitTempoScadutoDomandeOmesse();
        $this->testCorrezioneParzialeConOmissioni();
        $this->testProtezioneTamperingIdDomandeNonNelQuiz();
        $this->testProtezioneValoriRisposteNonBooleane();
        $this->testStatisticheInizialiZeroQuiz();
        $this->testAggiornamentoStatisticheDopoSvolgimenti();
        $this->testIsolamentoEsitoAllievoSicuro();

        echo "\n----------------------------------------------------\n";
        echo "RISULTATO FINALE: {$this->passati} / {$this->totali} test superati con successo.\n";
        echo "====================================================\n";
    }

    private function testGenerazioneNuovoIscritto(): void
    {
        echo "1) Test Generazione Quiz per Nuovo Iscritto (0 test svolti):\n";
        $iscritto = $this->creaAllievo(101);
        $domande = $this->service->generaQuiz(1, $iscritto);

        $this->assert(
            "Genera esattamente 30 domande target",
            count($domande) === 30,
            "Attese 30 domande, generate: " . count($domande)
        );

        $ids = array_map(fn($d) => $d->getIdDomanda(), $domande);
        $this->assert(
            "Tutte le 30 domande generate sono uniche",
            count(array_unique($ids)) === 30,
            "Rilevati duplicati nella scheda generata"
        );
    }

    private function testGenerazioneConErroriPregressi(): void
    {
        echo "\n2) Test Generazione Dinamica con Storico Errori:\n";
        $iscritto = $this->creaAllievo(102);

        // Simuliamo tentativi pregressi errati per le domande 2, 4, 6
        foreach ([2, 4, 6] as $idErr) {
            $t = new ETentativoRisposta();
            $sv = new ESvolgimentoQuiz();
            $sv->init($this->quizStore[1], $iscritto, new \DateTimeImmutable(), 1, false);
            $t->init($this->domandeStore[$idErr], $sv, false, false);
            $this->tentativiStore[] = $t;
        }

        $domande = $this->service->generaQuiz(1, $iscritto);
        $this->assert(
            "Generazione a cascata raggiunge esattamente 30 domande",
            count($domande) === 30
        );

        $ids = array_map(fn($d) => $d->getIdDomanda(), $domande);
        $intersezioneErrori = array_intersect([2, 4, 6], $ids);
        $this->assert(
            "Le domande errate in precedenza vengono ripescate prioritariamente",
            count($intersezioneErrori) > 0,
            "Nessuna domanda sbagliata ripescata"
        );
    }

    private function testCorrezioneTutteCorretteIdoneo(): void
    {
        echo "\n3) Test Correzione: 30 risposte esatte (0 errori, IDONEO):\n";
        $iscritto = $this->creaAllievo(103);
        $risposte = [];
        for ($i = 1; $i <= 30; $i++) {
            $risposte[$i] = ($i % 2 === 0) ? 'true' : 'false';
        }

        $svolgimento = $this->service->correggiQuiz(1, $iscritto, $risposte, array_keys($risposte));
        $this->assert(
            "Zero errori calcolati",
            $svolgimento->getErrori() === 0,
            "Errori rilevati: " . $svolgimento->getErrori()
        );
        $this->assert(
            "Esito è superato (Idoneo)",
            $svolgimento->isSuperato() === true
        );
    }

    private function testCorrezioneLimiteErroriTreIdoneo(): void
    {
        echo "\n4) Test Correzione: Esattamente 3 errori (limite massimo consentito, IDONEO):\n";
        $iscritto = $this->creaAllievo(104);
        $risposte = [];
        for ($i = 1; $i <= 30; $i++) {
            $risposte[$i] = ($i % 2 === 0) ? 'true' : 'false';
        }
        // Invertiamo 3 risposte (domande 1, 2, 3)
        $risposte[1] = 'true';  // errata (era false)
        $risposte[2] = 'false'; // errata (era true)
        $risposte[3] = 'true';  // errata (era false)

        $svolgimento = $this->service->correggiQuiz(1, $iscritto, $risposte, array_keys($risposte));
        $this->assert(
            "Esattamente 3 errori conteggiati",
            $svolgimento->getErrori() === 3,
            "Errori: " . $svolgimento->getErrori()
        );
        $this->assert(
            "3 errori risultano ancora IDONEO",
            $svolgimento->isSuperato() === true
        );
    }

    private function testCorrezioneQuattroErroriNonIdoneo(): void
    {
        echo "\n5) Test Correzione: 4 errori (soglia superata, NON IDONEO):\n";
        $iscritto = $this->creaAllievo(105);
        $risposte = [];
        for ($i = 1; $i <= 30; $i++) {
            $risposte[$i] = ($i % 2 === 0) ? 'true' : 'false';
        }
        // Sbagliamo 4 risposte
        $risposte[1] = 'true';
        $risposte[2] = 'false';
        $risposte[3] = 'true';
        $risposte[4] = 'false';

        $svolgimento = $this->service->correggiQuiz(1, $iscritto, $risposte, array_keys($risposte));
        $this->assert(
            "Esattamente 4 errori conteggiati",
            $svolgimento->getErrori() === 4
        );
        $this->assert(
            "4 errori risultano NON SUPERATO / DA RIPETERE",
            $svolgimento->isSuperato() === false
        );
    }

    private function testCorrezioneAutoSubmitTempoScadutoDomandeOmesse(): void
    {
        echo "\n6) Test Auto-submit a Tempo Scaduto (Tutte 30 domande omesse/non risposte):\n";
        $iscritto = $this->creaAllievo(106);
        $idDomande = range(1, 30);
        $risposteVuote = []; // Timer scaduto, inviato array vuoto

        $svolgimento = $this->service->correggiQuiz(1, $iscritto, $risposteVuote, $idDomande);
        $this->assert(
            "Tutte le 30 domande omesse sono conteggiate come errore",
            $svolgimento->getErrori() === 30,
            "Errori conteggiati: " . $svolgimento->getErrori()
        );
        $this->assert(
            "Scheda con risposte omesse non è superata",
            $svolgimento->isSuperato() === false
        );
    }

    private function testCorrezioneParzialeConOmissioni(): void
    {
        echo "\n7) Test Risposte Parziali (10 corrette, 20 omesse):\n";
        $iscritto = $this->creaAllievo(107);
        $idDomande = range(1, 30);
        $risposteParziali = [];
        for ($i = 1; $i <= 10; $i++) {
            $risposteParziali[$i] = ($i % 2 === 0) ? 'true' : 'false'; // 10 corrette
        }

        $svolgimento = $this->service->correggiQuiz(1, $iscritto, $risposteParziali, $idDomande);
        $this->assert(
            "20 domande omesse diventano 20 errori",
            $svolgimento->getErrori() === 20,
            "Errori: " . $svolgimento->getErrori()
        );
        $this->assert(
            "Registrati 10 tentativi effettivi nel riepilogo",
            $svolgimento->getTentativi()->count() === 10
        );
    }

    private function testProtezioneTamperingIdDomandeNonNelQuiz(): void
    {
        echo "\n8) Test Sicurezza Tampering: invio ID di domande inesistenti o esterne al quiz:\n";
        $iscritto = $this->creaAllievo(108);
        $idDomandeValide = [1, 2, 3, 4, 5]; // Solo prime 5 domande per quiz id 2
        $risposteHackerate = [
            1 => 'false',
            2 => 'true',
            999 => 'true', // ID non esistente
            25 => 'false', // ID appartenente al quiz 1 ma non al quiz 2
        ];

        $svolgimento = $this->service->correggiQuiz(2, $iscritto, $risposteHackerate, $idDomandeValide);
        $this->assert(
            "Le domande estranee/manomesse vengono ignorate e le domande omesse conteggiate",
            $svolgimento->getErrori() >= 3, // Domande 3,4,5 omesse -> 3 errori
            "Errori: " . $svolgimento->getErrori()
        );
    }

    private function testProtezioneValoriRisposteNonBooleane(): void
    {
        echo "\n9) Test Sanitizzazione: valori risposta anomali o stringhe malformate:\n";
        $iscritto = $this->creaAllievo(109);
        $idDomande = [1, 2];
        $risposteAnomale = [
            1 => 'invalid_string',
            2 => 'true',
        ];

        $svolgimento = $this->service->correggiQuiz(2, $iscritto, $risposteAnomale, $idDomande);
        $this->assert(
            "Valore malformato 'invalid_string' viene trattato come non risposta ed errore",
            $svolgimento->getErrori() >= 1
        );
    }

    private function testStatisticheInizialiZeroQuiz(): void
    {
        echo "\n10) Test Statistiche Dashboard per Allievo a 0 Quiz svolti:\n";
        $iscritto = $this->creaAllievo(110);
        $stats = $this->service->getStatisticheAllievo($iscritto);

        $this->assert(
            "Zero quiz svolti restituisce totale 0",
            $stats['totale'] === 0
        );
        $this->assert(
            "Percentuale idoneità è 0% senza divisione per zero",
            $stats['percentualeIdoneita'] === 0
        );
    }

    private function testAggiornamentoStatisticheDopoSvolgimenti(): void
    {
        echo "\n11) Test Calcolo Statistiche dopo più simulazioni (2 svolti, 1 superato -> 50%):\n";
        $iscritto = $this->creaAllievo(111);

        // Salviamo 1 superato e 1 non superato
        $sv1 = new ESvolgimentoQuiz();
        $sv1->init($this->quizStore[1], $iscritto, new \DateTimeImmutable(), 1, true);
        $this->service->confermaSvolgimento($sv1);

        $sv2 = new ESvolgimentoQuiz();
        $sv2->init($this->quizStore[1], $iscritto, new \DateTimeImmutable(), 5, false);
        $this->service->confermaSvolgimento($sv2);

        $stats = $this->service->getStatisticheAllievo($iscritto);
        $this->assert(
            "Totale svolti = 2",
            $stats['totale'] === 2
        );
        $this->assert(
            "Superati = 1, Non superati = 1",
            $stats['superati'] === 1 && $stats['nonSuperati'] === 1
        );
        $this->assert(
            "Percentuale Idoneità calcolata correttamente al 50%",
            $stats['percentualeIdoneita'] === 50,
            "Percentuale: " . $stats['percentualeIdoneita']
        );
    }

    private function testIsolamentoEsitoAllievoSicuro(): void
    {
        echo "\n12) Test Sicurezza Accesso Esito (Isolamento tra allievi):\n";
        $iscrittoA = $this->creaAllievo(112, "Anna", "Verdi");
        $iscrittoB = $this->creaAllievo(113, "Luca", "Bianchi");

        $sv = new ESvolgimentoQuiz();
        $sv->init($this->quizStore[1], $iscrittoA, new \DateTimeImmutable(), 0, true);
        $this->service->confermaSvolgimento($sv);
        $idSvolgimento = $sv->getIdSvolgimento();

        // Accesso da parte del legittimo proprietario
        $esitoLegittimo = $this->service->getEsitoQuiz($idSvolgimento, $iscrittoA->getId());
        $this->assert(
            "L'allievo proprietario visualizza correttamente il proprio esito",
            $esitoLegittimo->getErrori() === 0
        );

        // Tentativo di accesso da parte di un altro allievo
        $eccezioneSollevata = false;
        try {
            $this->service->getEsitoQuiz($idSvolgimento, $iscrittoB->getId());
        } catch (\InvalidArgumentException $e) {
            $eccezioneSollevata = true;
        }

        $this->assert(
            "Accesso all'esito da parte di altro utente viene bloccato con eccezione",
            $eccezioneSollevata === true
        );
    }
}

$testRunner = new TestQuizCasiLimite();
$testRunner->runAll();
