<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Service\SIscrizione;
use CamassoMedelago\DriveMeSafely\View\VIscrizione;
use Doctrine\ORM\EntityManagerInterface;

class CIscrizione
{
    private EntityManagerInterface $em;
    private SIscrizione $service;
    private VIscrizione $view;
    private string $contextPath;

    public function __construct(EntityManagerInterface $em, string $contextPath = '')
    {
        $this->em = $em;
        $this->service = new SIscrizione($em);
        $this->view = new VIscrizione();
        $this->contextPath = $contextPath;
    }
    /**
     * Gestisce il caso d'uso dell'iscrizione.
     *
     * In base al metodo HTTP ricevuto:
     *
     * GET  -> visualizzazione pacchetti / dettaglio / form
     * POST -> elaborazione del form di iscrizione
     */
    public function iscrizione(): void
    {
        switch ($_SERVER['REQUEST_METHOD']) {

            case 'GET':
                $this->get();
                break;

            case 'POST':
                $this->post();
                break;

            default:
                http_response_code(405);
                $this->view->showError(
                    "Metodo HTTP non supportato.",
                    405
                );
                break;
        }
    }
    /**
     * Gestisce tutte le richieste GET relative all'iscrizione.
     *
     * Possibili situazioni:
     *
     * 1. GET senza idPa
     *    -> visualizza tutti i pacchetti patente
     *
     * 2. GET con idPa
     *    -> visualizza il dettaglio del pacchetto selezionato
     *
     * 3. GET con idPa e parametro form
     *    -> visualizza il form di iscrizione
     */
    private function get(): void
    {
        try {

            $idPaParam = $_GET['idPa'] ?? null;

            //1)Caso in cui nessun pacchette viene selezionato
            if ($idPaParam === null || trim($idPaParam) === '') {
                $pacchetti = $this->service->getPatenti();
                $this->view->showPacchetti($pacchetti);
                return;
            }

            //Controllo id patente
            if (!ctype_digit((string) $idPaParam)) {
                $this->view->showError(
                    "Identificativo patente non valido.",
                    400
                );
                return;
            }
            $idPa = (int) $idPaParam;

            //2)Caso in cui un pacchetto è stato selezionato, recupero dei dettagli
            $pacchetto = $this->service->getPacchetto($idPa);
            if ($pacchetto === null) {
                $this->view->showError(
                    "Pacchetto non trovato.",
                    404
                );
                return;
            }
            
            //3)Caso in cui è stato richiesto il form di iscrizione con il pacchetto selezionato
            if (isset($_GET['form'])) {
                if ($this->isUtenteAutenticato()) {
                    header('Location: ' . $this->contextPath . '/home');
                    exit;
                }
                $this->view->showFormIscrizione($pacchetto);
                return;
            }

            //4)Caso in cui nessun parametro "form" è presente: mostriamo il dettaglio del pacchetto.

            $this->view->showDettaglioPacchetto($pacchetto);

        } catch (\InvalidArgumentException|\RuntimeException $e) {
            $this->view->showError(
                $e->getMessage(),
                400
            );

        } catch (\Throwable $e) {
            error_log(
                sprintf(
                    'Errore visualizzazione iscrizione: %s in %s:%d',
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                )
            );
            $this->view->showError(
                "Si è verificato un errore imprevisto durante la visualizzazione.",
                500
            );
        }
    }

    /**
     * Gestisce il POST del form di iscrizione.
     *
     * Legge esclusivamente i dati della HTTP request,
     * li normalizza e li passa al Service.
     *
     * La business logic rimane nel Service.
     */
    private function post(): void
    {
        $pacchetto = null;
        try {
            if ($this->isUtenteAutenticato()) {
                header('Location: ' . $this->contextPath . '/home');
                exit;
            }
            
            //1) Recupero ID patente selezionata
            $idPaParam = $_POST['idPa'] ?? null;

            if (
                $idPaParam === null ||
                !ctype_digit((string) $idPaParam)
            ) {
                throw new \InvalidArgumentException(
                    "Patente selezionata non valida."
                );
            }

            $idPa = (int) $idPaParam;
            $pacchetto = $this->service->getPacchetto($idPa);
            if ($pacchetto === null) {
                throw new \InvalidArgumentException(
                    "Pacchetto patente selezionato non valido."
                );
            }

            //2) Recupero dati account
            $username = $_POST['username'] ?? null;
            $email = $_POST['email'] ?? null;
            $password = $_POST['password'] ?? null;


            //3) Recupero dati anagrafici
            $nome = $_POST['nome'] ?? null;
            $cognome = $_POST['cognome'] ?? null;
            $cf = $_POST['codiceFiscale'] ?? null;
            $dataNascitaParam = $_POST['dataNascita'] ?? null;
            $luogoNascita = $_POST['luogoNascita'] ?? null;
            $indirizzo = $_POST['indirizzo'] ?? null;
            $telefono = $_POST['telefono'] ?? null;

            //4) Normalizzazione dei dati
            $username = $username !== null ? trim($username) : null;
            $email = $email !== null ? strtolower(trim($email)): null; 
            $nome = $nome !== null ? trim($nome) : null;
            $cognome = $cognome !== null ? trim($cognome)  : null;
            $cf = $cf !== null ? strtoupper(trim($cf)): null;
            $luogoNascita = $luogoNascita !== null
                ? trim($luogoNascita)
                : null;
            $indirizzo = $indirizzo !== null
                ? trim($indirizzo)
                : null;
            $telefono = $telefono !== null
                ? trim($telefono)
                : null;

            //5) Controllo presenza di tutti i campi obbligatori
            // (evita un TypeError quando un campo mancante viene passato
            // come null a un parametro string non-nullable del Service)
            if (
                $username === null || $username === '' ||
                $email === null || $email === '' ||
                $password === null || $password === '' ||
                $nome === null || $nome === '' ||
                $cognome === null || $cognome === '' ||
                $cf === null || $cf === '' ||
                $luogoNascita === null || $luogoNascita === '' ||
                $indirizzo === null || $indirizzo === '' ||
                $telefono === null || $telefono === ''
            ) {
                throw new \InvalidArgumentException(
                    "Tutti i campi del modulo di iscrizione sono obbligatori."
                );
            }

            //6) Conversione data di nascita
            if (
                $dataNascitaParam === null ||
                trim($dataNascitaParam) === ''
            ) {
                throw new \InvalidArgumentException(
                    "Data di nascita non valida."
                );
            }

            $dataNascita = \DateTimeImmutable::createFromFormat('!Y-m-d', trim($dataNascitaParam));
            if ($dataNascita === false || $dataNascita->format('Y-m-d') !== trim($dataNascitaParam)) {
                throw new \InvalidArgumentException(
                    "Formato data di nascita non valido (richiesto AAAA-MM-GG)."
                );
            }
            //7) BUSINESS LOGIC
            // Tutta la logica dell'iscrizione viene delegata al Service.

            $iscritto = $this->service->iscrizione(
                $idPa,
                $nome,
                $cognome,
                $username,
                $email,
                $password,
                $cf,
                $indirizzo,
                $luogoNascita,
                $dataNascita,
                $telefono
            );

            //8) Salvataggio definitivo
            $this->service->confermaIscrizione(
                $iscritto
            );

            //9) Login automatico
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            session_regenerate_id(true);
            $_SESSION['utenteLoggatoId'] = $iscritto->getId();
            
            //10) REDIRECT ALLA HOME
            header('Location: ' . $this->contextPath . '/');
            exit;


        } catch (\InvalidArgumentException|\RuntimeException $e) {
            $this->view->showFormError(
                $e->getMessage(),
                $_POST,
                $pacchetto
            );

        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            $this->view->showFormError(
                "Username, email o codice fiscale già presenti.",
                $_POST,
                $pacchetto
            );

        } catch (\Throwable $e) {
            error_log(
                sprintf(
                    'Errore iscrizione: %s in %s:%d',
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                )
            );
            $this->view->showError(
                "Si è verificato un errore imprevisto durante l'iscrizione.",
                500
            );
        }
    }

    private function isUtenteAutenticato(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $utenteId = $_SESSION['utenteLoggatoId'] ?? null;

        return is_int($utenteId) || (is_string($utenteId) && ctype_digit($utenteId) && (int) $utenteId > 0);
    }
}
