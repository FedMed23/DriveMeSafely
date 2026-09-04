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
use CamassoMedelago\DriveMeSafely\Foundation\FLezione;
use CamassoMedelago\DriveMeSafely\Foundation\FPrenotazioneLezione;
use CamassoMedelago\DriveMeSafely\Service\SLezione;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Connection;
use DateTimeImmutable;

class TestLezioniCasiLimite
{
    private SLezione $service;
    public array $lezioniStore = [];
    public array $prenotazioniStore = [];
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

        $this->service = new SLezione($emMock);
        $reflection = new \ReflectionClass($this->service);

        // Mock FLezione
        $fLezioneMock = new class($test, $emMock) extends FLezione {
            private TestLezioniCasiLimite $test;
            public function __construct(TestLezioniCasiLimite $test, EntityManagerInterface $em) {
                parent::__construct($em);
                $this->test = $test;
            }
            public function findAllPalinsesto(): array {
                return array_values($this->test->lezioniStore);
            }
            public function findById(int $idLezione): ?ELezione {
                return $this->test->lezioniStore[$idLezione] ?? null;
            }
            public function findByIdForUpdate(int $idLezione): ?ELezione {
                return $this->findById($idLezione);
            }
            public function save(ELezione $lezione): void {
                if ($lezione->getIdLezione() === null) {
                    $id = count($this->test->lezioniStore) + 1;
                    $lezione->setIdLezione($id);
                }
                $this->test->lezioniStore[$lezione->getIdLezione()] = $lezione;
            }
            public function delete(ELezione $lezione): void {
                unset($this->test->lezioniStore[$lezione->getIdLezione()]);
            }
            public function istruttoreVeicoloInUso(DateTimeImmutable $dataOra, string $istruttore, string $vettura, int $durataMinuti = 60): bool {
                $istruttoreNorm = mb_strtoupper(preg_replace('/\s+/', ' ', trim($istruttore)));
                $vetturaNorm = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $vettura));
                $inizioNuova = $dataOra;
                $fineNuova = $dataOra->modify("+{$durataMinuti} minutes");

                foreach ($this->test->lezioniStore as $l) {
                    if (!$l instanceof ELezionePratica) continue;
                    $iExist = mb_strtoupper(preg_replace('/\s+/', ' ', trim((string)$l->getIstruttore())));
                    $vExist = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string)$l->getVettura()));

                    if ($iExist === $istruttoreNorm || $vExist === $vetturaNorm) {
                        $inizioEsistente = $l->getDataOra();
                        $fineEsistente = $inizioEsistente->modify("+{$durataMinuti} minutes");
                        if ($inizioNuova < $fineEsistente && $inizioEsistente < $fineNuova) {
                            return true;
                        }
                    }
                }
                return false;
            }
            public function aulaInUso(DateTimeImmutable $dataOra, string $aula, int $durataMinuti = 60): bool {
                $aulaEnum = EAula::tryFrom($aula);
                if ($aulaEnum === null) return false;
                $inizioNuova = $dataOra;
                $fineNuova = $dataOra->modify("+{$durataMinuti} minutes");

                foreach ($this->test->lezioniStore as $l) {
                    if (!$l instanceof ELezioneTeoria) continue;
                    if ($l->getAula() === $aulaEnum) {
                        $inizioEsistente = $l->getDataOra();
                        $fineEsistente = $inizioEsistente->modify("+{$durataMinuti} minutes");
                        if ($inizioNuova < $fineEsistente && $inizioEsistente < $fineNuova) {
                            return true;
                        }
                    }
                }
                return false;
            }
        };

        // Mock FPrenotazioneLezione
        $fPrenotazioneMock = new class($test, $emMock) extends FPrenotazioneLezione {
            private TestLezioniCasiLimite $test;
            public function __construct(TestLezioniCasiLimite $test, EntityManagerInterface $em) {
                parent::__construct($em);
                $this->test = $test;
            }
            public function haPrenotazioniPerLezione(int $idLezione, bool $soloAttive = false): bool {
                foreach ($this->test->prenotazioniStore as $p) {
                    /** @var EPrenotazioneLezione $p */
                    if ($p->getLezione()->getIdLezione() === $idLezione) {
                        if (!$soloAttive || $p->getStato() === StatoPrenotazione::PRENOTATA) {
                            return true;
                        }
                    }
                }
                return false;
            }
            public function annullaPrenotazioniPerLezione(int $idLezione): void {
                foreach ($this->test->prenotazioniStore as $p) {
                    /** @var EPrenotazioneLezione $p */
                    if ($p->getLezione()->getIdLezione() === $idLezione && $p->getStato() === StatoPrenotazione::PRENOTATA) {
                        $p->setStato(StatoPrenotazione::ANNULLATA);
                    }
                }
            }
        };

        $propFLezione = $reflection->getProperty('fLezione');
        $propFLezione->setAccessible(true);
        $propFLezione->setValue($this->service, $fLezioneMock);

        $propFPren = $reflection->getProperty('fPrenotazione');
        $propFPren->setAccessible(true);
        $propFPren->setValue($this->service, $fPrenotazioneMock);
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

    private function generaDataFuturaLavorativa(int $giorni = 10, int $ora = 10, int $minuti = 0): DateTimeImmutable
    {
        $data = (new DateTimeImmutable())->modify("+{$giorni} days")->setTime($ora, $minuti, 0);
        while ((int) $data->format('N') >= 6) {
            $data = $data->modify('+1 day');
        }
        return $data;
    }

    public function runAll(): void
    {
        echo "====================================================\n";
        echo "   TEST SUITE: FLUSSO E CASI LIMITE GESTIONE LEZIONI\n";
        echo "====================================================\n\n";

        $this->testInserimentoLezionePraticaValida();
        $this->testInserimentoLezioneTeoricaValida();
        $this->testGranularitaMinutiNonAllineati();
        $this->testSovrapposizioneOrariaSlot();
        $this->testNormalizzazioneIstruttoreETarga();
        $this->testFasciaLavorativaEPausaPranzo();
        $this->testEliminazioneSenzaPrenotazioni();
        $this->testBloccoEliminazioneConPrenotazioniAttive();
        $this->testAnnullamentoLezioneConPrenotazioni();
        $this->testBloccoEliminazioneLezioniPassate();

        echo "\n----------------------------------------------------\n";
        echo "RISULTATO FINALE: {$this->passati} / {$this->totali} test superati con successo.\n";
        echo "====================================================\n";
    }

    private function testInserimentoLezionePraticaValida(): void
    {
        echo "1) Test Inserimento Guida Pratica Valida:\n";
        $data = $this->generaDataFuturaLavorativa(5, 10, 0);
        $lezione = $this->service->inserisciLezione([
            'tipoLezione' => 'PRATICA',
            'dataOra' => $data->format('Y-m-d H:i'),
            'istruttore' => 'Mario Rossi',
            'vettura' => 'AB123CD',
        ]);
        $this->service->confermaLezione($lezione);

        $this->assert("Lezione pratica creata e salvata con successo", $lezione instanceof ELezionePratica);
        $this->assert("Istruttore e vettura assegnati correttamente", $lezione->getIstruttore() === 'Mario Rossi' && $lezione->getVettura() === 'AB123CD');
    }

    private function testInserimentoLezioneTeoricaValida(): void
    {
        echo "\n2) Test Inserimento Lezione di Teoria Valida:\n";
        $data = $this->generaDataFuturaLavorativa(6, 11, 0);
        $lezione = $this->service->inserisciLezione([
            'tipoLezione' => 'TEORIA',
            'dataOra' => $data->format('Y-m-d H:i'),
            'aula' => EAula::AULA_A->value,
            'argomento' => EArgomentoMinisteriale::SEGNALETICA->value,
        ]);
        $this->service->confermaLezione($lezione);

        $this->assert("Lezione teoria creata e salvata", $lezione instanceof ELezioneTeoria);
        $this->assert("Aula e argomento validati come Enum", $lezione->getAula() === EAula::AULA_A && $lezione->getArgomentoLezione() === EArgomentoMinisteriale::SEGNALETICA);
    }

    private function testGranularitaMinutiNonAllineati(): void
    {
        echo "\n3) Test Granularità Minuti Non Allineati (es. 10:17):\n";
        $data = $this->generaDataFuturaLavorativa(7, 10, 17);
        $eccezione = false;
        try {
            $this->service->inserisciLezione([
                'tipoLezione' => 'PRATICA',
                'dataOra' => $data->format('Y-m-d H:i'),
                'istruttore' => 'Luigi Bianchi',
                'vettura' => 'CD456EF',
            ]);
        } catch (\InvalidArgumentException $e) {
            $eccezione = true;
        }
        $this->assert("Minuti non allineati a :00 o :30 vengono rifiutati", $eccezione);
    }

    private function testSovrapposizioneOrariaSlot(): void
    {
        echo "\n4) Test Rilevamento Conflitto e Sovrapposizione Oraria (Overlapping):\n";
        $dataBase = $this->generaDataFuturaLavorativa(8, 15, 0);
        $l1 = $this->service->inserisciLezione([
            'tipoLezione' => 'PRATICA',
            'dataOra' => $dataBase->format('Y-m-d H:i'),
            'istruttore' => 'Gianni Verdi',
            'vettura' => 'EF789GH',
        ]);
        $this->service->confermaLezione($l1);

        // Tentativo di inserire una guida alle 15:30 con lo stesso istruttore (conflitto perché la prima dura fino alle 16:00)
        $dataSovrapposta = $dataBase->modify('+30 minutes');
        $eccezioneIstruttore = false;
        try {
            $this->service->inserisciLezione([
                'tipoLezione' => 'PRATICA',
                'dataOra' => $dataSovrapposta->format('Y-m-d H:i'),
                'istruttore' => 'Gianni Verdi',
                'vettura' => 'XX999YY', // altra vettura ma stesso istruttore
            ]);
        } catch (\InvalidArgumentException $e) {
            $eccezioneIstruttore = true;
        }
        $this->assert("Guida sovrapposta con stesso istruttore viene bloccata", $eccezioneIstruttore);

        // Tentativo di inserire una guida alle 15:30 con la stessa vettura
        $eccezioneVettura = false;
        try {
            $this->service->inserisciLezione([
                'tipoLezione' => 'PRATICA',
                'dataOra' => $dataSovrapposta->format('Y-m-d H:i'),
                'istruttore' => 'Altro Istruttore',
                'vettura' => 'EF789GH', // stessa vettura
            ]);
        } catch (\InvalidArgumentException $e) {
            $eccezioneVettura = true;
        }
        $this->assert("Guida sovrapposta con stessa vettura viene bloccata", $eccezioneVettura);
    }

    private function testNormalizzazioneIstruttoreETarga(): void
    {
        echo "\n5) Test Normalizzazione Istruttore e Targa (Spazi e Caratteri Speciali):\n";
        $dataBase = $this->generaDataFuturaLavorativa(9, 16, 0);
        $l = $this->service->inserisciLezione([
            'tipoLezione' => 'PRATICA',
            'dataOra' => $dataBase->format('Y-m-d H:i'),
            'istruttore' => '   Roberto   Neri   ',
            'vettura' => '  ab-999-cd  ',
        ]);
        $this->service->confermaLezione($l);

        $this->assert("Nome istruttore normalizzato senza spazi multipli", $l->getIstruttore() === 'Roberto Neri');
        $this->assert("Targa vettura normalizzata in maiuscolo e alfanumerica", $l->getVettura() === 'AB999CD');

        // Test conflitto con scrittura differente
        $eccezione = false;
        try {
            $this->service->inserisciLezione([
                'tipoLezione' => 'PRATICA',
                'dataOra' => $dataBase->format('Y-m-d H:i'),
                'istruttore' => 'roberto neri',
                'vettura' => 'ZZ000ZZ',
            ]);
        } catch (\InvalidArgumentException $e) {
            $eccezione = true;
        }
        $this->assert("Conflitto rilevato anche con maiuscole/minuscole differenti", $eccezione);
    }

    private function testFasciaLavorativaEPausaPranzo(): void
    {
        echo "\n6) Test Fascia Lavorativa e Pausa Pranzo:\n";
        // Pausa pranzo 13:00
        $dataPranzo = $this->generaDataFuturaLavorativa(11, 13, 0);
        $errPranzo = false;
        try {
            $this->service->inserisciLezione([
                'tipoLezione' => 'TEORIA',
                'dataOra' => $dataPranzo->format('Y-m-d H:i'),
                'aula' => EAula::AULA_A->value,
                'argomento' => EArgomentoMinisteriale::VELOCITA->value,
            ]);
        } catch (\InvalidArgumentException) {
            $errPranzo = true;
        }
        $this->assert("Slot alle 13:00 (pausa pranzo) rifiutato", $errPranzo);

        // Slot alle 12:30 (sconfina nella pausa pranzo 13:00-14:00)
        $data1230 = $this->generaDataFuturaLavorativa(11, 12, 30);
        $err1230 = false;
        try {
            $this->service->inserisciLezione([
                'tipoLezione' => 'TEORIA',
                'dataOra' => $data1230->format('Y-m-d H:i'),
                'aula' => EAula::AULA_A->value,
                'argomento' => EArgomentoMinisteriale::VELOCITA->value,
            ]);
        } catch (\InvalidArgumentException) {
            $err1230 = true;
        }
        $this->assert("Slot alle 12:30 che sconfina nella pausa pranzo rifiutato", $err1230);
    }

    private function testEliminazioneSenzaPrenotazioni(): void
    {
        echo "\n7) Test Eliminazione Lezione Senza Prenotazioni:\n";
        $data = $this->generaDataFuturaLavorativa(12, 10, 0);
        $l = $this->service->inserisciLezione([
            'tipoLezione' => 'PRATICA',
            'dataOra' => $data->format('Y-m-d H:i'),
            'istruttore' => 'Istruttore Libero',
            'vettura' => 'AA000AA',
        ]);
        $this->service->confermaLezione($l);
        $id = $l->getIdLezione();

        $this->service->eliminaLezione($id);
        $this->assert("Lezione eliminata con successo dal palinsesto", !isset($this->lezioniStore[$id]));
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

        return $iscritto;
    }

    private function testBloccoEliminazioneConPrenotazioniAttive(): void
    {
        echo "\n8) Test Blocco Eliminazione Diretta se ci sono Prenotazioni:\n";
        $data = $this->generaDataFuturaLavorativa(13, 11, 0);
        $l = $this->service->inserisciLezione([
            'tipoLezione' => 'PRATICA',
            'dataOra' => $data->format('Y-m-d H:i'),
            'istruttore' => 'Istruttore Occupato',
            'vettura' => 'BB111BB',
        ]);
        $this->service->confermaLezione($l);
        $id = $l->getIdLezione();

        // Simuliamo prenotazione allievo
        $allievo = $this->creaAllievo(99, 'Paolo', 'Gialli');
        $prenotazione = EPrenotazioneLezione::crea($allievo, $l, StatoPrenotazione::PRENOTATA);
        $this->prenotazioniStore[] = $prenotazione;

        $bloccato = false;
        try {
            $this->service->eliminaLezione($id);
        } catch (\InvalidArgumentException $e) {
            $bloccato = true;
        }
        $this->assert("Eliminazione bloccata se sono presenti prenotazioni", $bloccato);
    }

    private function testAnnullamentoLezioneConPrenotazioni(): void
    {
        echo "\n9) Test Annullamento Lezione e Aggiornamento Stato Prenotazioni:\n";
        $data = $this->generaDataFuturaLavorativa(14, 15, 0);
        $l = $this->service->inserisciLezione([
            'tipoLezione' => 'PRATICA',
            'dataOra' => $data->format('Y-m-d H:i'),
            'istruttore' => 'Istruttore Annullando',
            'vettura' => 'CC222CC',
        ]);
        $this->service->confermaLezione($l);
        $id = $l->getIdLezione();

        $allievo = $this->creaAllievo(100, 'Chiara', 'Blu');
        $prenotazione = EPrenotazioneLezione::crea($allievo, $l, StatoPrenotazione::PRENOTATA);
        $this->prenotazioniStore[] = $prenotazione;

        $this->service->annullaLezione($id);
        $this->assert("Tutte le prenotazioni collegate sono passate allo stato ANNULLATA", $prenotazione->getStato() === StatoPrenotazione::ANNULLATA);
    }

    private function testBloccoEliminazioneLezioniPassate(): void
    {
        echo "\n10) Test Blocco Eliminazione o Annullamento per Lezioni Passate:\n";
        $lezionePassata = new ELezionePratica(new DateTimeImmutable('-2 days'), 'Istruttore Storico', 'DD333DD');
        $lezionePassata->setIdLezione(999);
        $this->lezioniStore[999] = $lezionePassata;

        $errElimina = false;
        try {
            $this->service->eliminaLezione(999);
        } catch (\InvalidArgumentException) {
            $errElimina = true;
        }
        $this->assert("Eliminazione lezione passata bloccata", $errElimina);

        $errAnnulla = false;
        try {
            $this->service->annullaLezione(999);
        } catch (\InvalidArgumentException) {
            $errAnnulla = true;
        }
        $this->assert("Annullamento lezione passata bloccato", $errAnnulla);
    }
}

$testRunner = new TestLezioniCasiLimite();
$testRunner->runAll();
