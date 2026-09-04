<?php

namespace CamassoMedelago\DriveMeSafely;

require_once __DIR__ . '/vendor/autoload.php';

use CamassoMedelago\DriveMeSafely\Service\SIscrizione;
use CamassoMedelago\DriveMeSafely\Entity\EPatente;
use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Entity\EUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FPatente;
use CamassoMedelago\DriveMeSafely\Foundation\FUtenteRegistrato;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class TestIscrizioneCasiLimite
{
    private SIscrizione $service;
    private $em;
    private array $allieviRegistrati = [];
    private array $utentiRegistrati = [];
    private array $patenti = [];
    private int $passati = 0;
    private int $totali = 0;

    public function __construct()
    {
        // Mocking EntityManager e Foundations
        $this->patenti = [
            1 => new EPatente('AM', 'Patente Ciclomotori'),
            2 => new EPatente('A1', 'Patente 125'),
            3 => new EPatente('B', 'Patente Autoveicoli'),
            4 => new EPatente('C', 'Patente Autocarri'),
            5 => new EPatente('A', 'Patente Moto Illimitata'),
        ];
        $this->patenti[1]->setId(1);
        $this->patenti[2]->setId(2);
        $this->patenti[3]->setId(3);
        $this->patenti[4]->setId(4);
        $this->patenti[5]->setId(5);

        // Pre-popola un dipendente e un iscritto
        $this->utentiRegistrati['admin_dipendente'] = 'dipendente@scuolaguida.it';
        $this->allieviRegistrati['RSSMRA00A01H501Z'] = 'mario.rossi@email.it';

        $emMock = $this->createMockEntityManager();
        $this->service = new SIscrizione($emMock);

        // Iniettiamo foundation mockate via reflection per test isolati e affidabili
        $reflection = new \ReflectionClass($this->service);
        
        $fPatenteMock = $this->createMockFPatente();
        $fIscrittoMock = $this->createMockFIscritto();
        $fUtenteMock = $this->createMockFUtente();

        $propPatente = $reflection->getProperty('fPatente');
        $propPatente->setAccessible(true);
        $propPatente->setValue($this->service, $fPatenteMock);

        $propIscritto = $reflection->getProperty('fIscritto');
        $propIscritto->setAccessible(true);
        $propIscritto->setValue($this->service, $fIscrittoMock);

        $propUtente = $reflection->getProperty('fUtente');
        $propUtente->setAccessible(true);
        $propUtente->setValue($this->service, $fUtenteMock);
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
            public function getConnection() { return null; }
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

    private function createMockFPatente(): FPatente
    {
        $test = $this;
        return new class($test->patenti) extends FPatente {
            private array $pList;
            public function __construct(array $pList) { $this->pList = $pList; }
            public function findPacchettoById(int $id): ?EPatente {
                return $this->pList[$id] ?? null;
            }
        };
    }

    private function createMockFIscritto(): FIscritto
    {
        $test = $this;
        return new class($test->allieviRegistrati) extends FIscritto {
            private array $allievi;
            public function __construct(array $allievi) { $this->allievi = $allievi; }
            public function existsByCF(string $cf): bool {
                return isset($this->allievi[$cf]);
            }
            public function findByCF(string $cf): ?EIscritto { return null; }
            public function findByUsername(string $username): ?EIscritto { return null; }
            public function findByEmail(string $email): ?EIscritto { return null; }
        };
    }

    private function createMockFUtente(): FUtenteRegistrato
    {
        $test = $this;
        return new class($test->utentiRegistrati) extends FUtenteRegistrato {
            private array $utenti;
            public function __construct(array $utenti) { $this->utenti = $utenti; }
            public function getByUsername(string $username): ?EUtenteRegistrato {
                if (isset($this->utenti[$username])) {
                    return new class('Mario', 'Rossi', 'mario@test.it', $username, 'Pass123!?', true) extends EUtenteRegistrato {};
                }
                return null;
            }
            public function getByEmail(string $email): ?EUtenteRegistrato {
                if (in_array($email, $this->utenti, true)) {
                    return new class('Mario', 'Rossi', $email, 'mario_user', 'Pass123!?', true) extends EUtenteRegistrato {};
                }
                return null;
            }
        };
    }

    private function assertThrows(string $descrizione, callable $fn, string $expectedException = \Exception::class): void
    {
        $this->totali++;
        try {
            $fn();
            echo "❌ [FAIL] {$descrizione} - Nessuna eccezione sollevata!\n";
        } catch (\Throwable $e) {
            if ($e instanceof $expectedException) {
                echo "✅ [PASS] {$descrizione} - Bloccato correttamente: " . $e->getMessage() . "\n";
                $this->passati++;
            } else {
                echo "❌ [FAIL] {$descrizione} - Eccezione inattesa: " . get_class($e) . ": " . $e->getMessage() . "\n";
            }
        }
    }

    private function assertSucceeds(string $descrizione, callable $fn): void
    {
        $this->totali++;
        try {
            $res = $fn();
            echo "✅ [PASS] {$descrizione}\n";
            $this->passati++;
        } catch (\Throwable $e) {
            echo "❌ [FAIL] {$descrizione} - Errore: " . $e->getMessage() . "\n";
        }
    }

    public function runAllTests(): void
    {
        echo "\n=======================================================\n";
        echo "🚀 AVVIO TEST CASI LIMITE - FLUSSO ISCRIZIONE\n";
        echo "=======================================================\n\n";

        // 1. Unicità Utente nella Gerarchia
        echo "--- 1. TEST UNICITA SU GERARCHIA UTENTI (EUtenteRegistrato) ---\n";
        $this->assertThrows(
            "Username già occupato da dipendente",
            fn() => $this->service->iscrizione(
                3, 'Luca', 'Bianchi', 'admin_dipendente', 'nuova@email.it', 'Pass123!?',
                'BNCLCU00A01H501U', 'Via Roma 1', 'L\'Aquila', new DateTimeImmutable('-20 years'), '+39 3331234567'
            ),
            \RuntimeException::class
        );

        $this->assertThrows(
            "Email già occupata da dipendente",
            fn() => $this->service->iscrizione(
                3, 'Luca', 'Bianchi', 'luca_nuovo', 'dipendente@scuolaguida.it', 'Pass123!?',
                'BNCLCU00A01H501U', 'Via Roma 1', 'L\'Aquila', new DateTimeImmutable('-20 years'), '+39 3331234567'
            ),
            \RuntimeException::class
        );

        // 2. Età Minima Parametrata per Tipo Patente
        echo "\n--- 2. TEST ETA MINIMA PER TIPO PATENTE ---\n";
        
        // Patente AM (14 anni)
        $this->assertSucceeds(
            "Patente AM con ragazzo di 14 anni",
            fn() => $this->service->iscrizione(
                1, 'Marco', 'Verdi', 'marco_am', 'marco_am@email.it', 'Pass123!?',
                'VRDMRC12A01H501U', 'Via Garibaldi 2', 'Roma', new DateTimeImmutable('-14 years'), '+39 3331234567'
            )
        );

        $this->assertThrows(
            "Patente AM con ragazzo di 13 anni",
            fn() => $this->service->iscrizione(
                1, 'Marco', 'Verdi', 'marco_am13', 'marco_am13@email.it', 'Pass123!?',
                'VRDMRC13A01H501U', 'Via Garibaldi 2', 'Roma', new DateTimeImmutable('-13 years'), '+39 3331234567'
            ),
            \InvalidArgumentException::class
        );

        // Patente B (18 anni)
        $this->assertThrows(
            "Patente B con ragazzo di 16 anni (deve fallire)",
            fn() => $this->service->iscrizione(
                3, 'Fabio', 'Neri', 'fabio16', 'fabio16@email.it', 'Pass123!?',
                'NREFBA10A01H501U', 'Via Milano 5', 'Milano', new DateTimeImmutable('-16 years'), '+39 3331234567'
            ),
            \InvalidArgumentException::class
        );

        $this->assertSucceeds(
            "Patente B con ragazzo di 18 anni",
            fn() => $this->service->iscrizione(
                3, 'Fabio', 'Neri', 'fabio18', 'fabio18@email.it', 'Pass123!?',
                'NREFBA08A01H501U', 'Via Milano 5', 'Milano', new DateTimeImmutable('-18 years'), '+39 3331234567'
            )
        );

        // Patente A (24 anni per accesso diretto)
        $this->assertThrows(
            "Patente A con ragazzo di 20 anni (deve fallire)",
            fn() => $this->service->iscrizione(
                5, 'Giovanni', 'Bruni', 'gio_moto', 'gio_moto@email.it', 'Pass123!?',
                'BRNGNN06A01H501U', 'Via Napoli 10', 'Napoli', new DateTimeImmutable('-20 years'), '+39 3331234567'
            ),
            \InvalidArgumentException::class
        );

        // 3. Casi Limite Data di Nascita
        echo "\n--- 3. TEST CASI LIMITE DATA DI NASCITA ---\n";
        $this->assertThrows(
            "Data nel passato remoto (150 anni fa)",
            fn() => $this->service->iscrizione(
                3, 'Nonno', 'Antico', 'nonno_antico', 'nonno@email.it', 'Pass123!?',
                'NTCANN76A01H501U', 'Via Antica 1', 'Roma', new DateTimeImmutable('-150 years'), '+39 3331234567'
            ),
            \InvalidArgumentException::class
        );

        // 4. Casi Limite Numero di Telefono
        echo "\n--- 4. TEST CASI LIMITE TELEFONO ---\n";
        $this->assertThrows(
            "Telefono con soli spazi '          '",
            fn() => $this->service->iscrizione(
                3, 'Paolo', 'Test', 'paolo_spazi', 'paolo_spazi@email.it', 'Pass123!?',
                'TSTPLA00A01H501U', 'Via Roma 1', 'Roma', new DateTimeImmutable('-20 years'), '          '
            ),
            \InvalidArgumentException::class
        );

        $this->assertThrows(
            "Telefono con solo 5 cifre '12345'",
            fn() => $this->service->iscrizione(
                3, 'Paolo', 'Test', 'paolo_corto', 'paolo_corto@email.it', 'Pass123!?',
                'TSTPLA00A01H501U', 'Via Roma 1', 'Roma', new DateTimeImmutable('-20 years'), '12345'
            ),
            \InvalidArgumentException::class
        );

        $this->assertSucceeds(
            "Telefono valido con prefisso '+39 333 1234567'",
            fn() => $this->service->iscrizione(
                3, 'Paolo', 'Test', 'paolo_ok', 'paolo_ok@email.it', 'Pass123!?',
                'TSTPLA00A01H501U', 'Via Roma 1', 'Roma', new DateTimeImmutable('-20 years'), '+39 333 1234567'
            )
        );

        // 5. Casi Limite Username
        echo "\n--- 5. TEST CASI LIMITE USERNAME ---\n";
        $this->assertThrows(
            "Username troppo corto (2 caratteri 'ab')",
            fn() => $this->service->iscrizione(
                3, 'Anna', 'Gialli', 'ab', 'anna@email.it', 'Pass123!?',
                'GLLNNA00A01H501U', 'Via Po 3', 'Torino', new DateTimeImmutable('-20 years'), '+39 3331234567'
            ),
            \InvalidArgumentException::class
        );

        $this->assertThrows(
            "Username con caratteri non consentiti 'user@name!'",
            fn() => $this->service->iscrizione(
                3, 'Anna', 'Gialli', 'user@name!', 'anna@email.it', 'Pass123!?',
                'GLLNNA00A01H501U', 'Via Po 3', 'Torino', new DateTimeImmutable('-20 years'), '+39 3331234567'
            ),
            \InvalidArgumentException::class
        );

        $this->assertSucceeds(
            "Username valido 'user_valid.1-2'",
            fn() => $this->service->iscrizione(
                3, 'Anna', 'Gialli', 'user_valid.1-2', 'anna@email.it', 'Pass123!?',
                'GLLNNA00A01H501U', 'Via Po 3', 'Torino', new DateTimeImmutable('-20 years'), '+39 3331234567'
            )
        );

        // 6. Nomi e Cognomi con Apostrofi e Caratteri Accentuati
        echo "\n--- 6. TEST NOMI/COGNOMI CON APOSTROFI E ACCENTI ---\n";
        $this->assertSucceeds(
            "Nome e Cognome con apostrofi 'D'Angelo Valèrie'",
            fn() => $this->service->iscrizione(
                3, 'Valèrie', 'D\'Angelo', 'valerie_dangelo', 'valerie@email.it', 'Pass123!?',
                'DNGLVR00A01H501U', 'Via D\'Annunzio 15', 'L\'Aquila', new DateTimeImmutable('-22 years'), '+39 3331234567'
            )
        );

        echo "\n=======================================================\n";
        echo "📊 ESITO FINALE: {$this->passati}/{$this->totali} test superati con successo!\n";
        echo "=======================================================\n\n";
    }
}

$testRunner = new TestIscrizioneCasiLimite();
$testRunner->runAllTests();
