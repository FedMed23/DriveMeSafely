<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Service\SIscrizione;
use CamassoMedelago\DriveMeSafely\View\VIscrizione;
use CamassoMedelago\DriveMeSafely\Utils\PasswordUtil;

class CIscrizione
{
    private EntityManager $em;
    private SIscrizione $service;
    private VIscrizione $view;

    public function __construct(EntityManager $em
    ) {
        $this->em = $em;
        $this->service = $service;
        $this->view = $view;
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


            /*
             * ---------------------------------------------------------
             * CASO 1
             * Nessun pacchetto selezionato.
             *
             * Equivalente alla PacchettiPatentiServlet:
             *
             * service.getPatenti()
             * ---------------------------------------------------------
             */

            if ($idPaParam === null || trim($idPaParam) === '') {
                $pacchetti = $this->service->getPatenti();
                $this->view->showPacchetti($pacchetti);
                return;
            }
            /*
             * ---------------------------------------------------------
             * Controllo dell'id della patente
             * ---------------------------------------------------------
             */

            if (!ctype_digit((string) $idPaParam)) {
                $this->view->showError(
                    "Identificativo patente non valido.",
                    400
                );
                return;
            }
            $idPa = (int) $idPaParam;


            /*
             * ---------------------------------------------------------
             * CASO 2 / 3
             *
             * Recuperiamo il pacchetto selezionato.
             * ---------------------------------------------------------
             */
            $pacchetto = $this->service->getPacchetto($idPa);
            if ($pacchetto === null) {
                $this->view->showError(
                    "Pacchetto non trovato.",
                    404
                );
                return;
            }
            /*
             * ---------------------------------------------------------
             * CASO 3
             *
             * Se è stato richiesto il form di iscrizione,
             * visualizziamo il form passando il pacchetto.
             *
             * Esempio:
             *
             * /iscrizione?idPa=1&form=1
             * ---------------------------------------------------------
             */
            if (isset($_GET['form'])) {
                $this->view->showFormIscrizione($pacchetto);
                return;
            }

            /*
             * ---------------------------------------------------------
             * CASO 2
             *
             * Nessun parametro "form":
             * mostriamo il dettaglio del pacchetto.
             * ---------------------------------------------------------
             */

            $this->view->showDettaglioPacchetto($pacchetto);

    } catch (\InvalidArgumentException|\RuntimeException $e) {
    $pacchetto = null;

    if (isset($idPa) && $idPa > 0) {
        $pacchetto = $this->service->getPacchetto($idPa);
    }

    $this->view->showFormError(
        $e->getMessage(),
        $_POST,
        $pacchetto
    );

    } catch (\Throwable $e) {
        $this->view->showError(
            "Si è verificato un errore imprevisto durante l'iscrizione.",
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
        try {
            /*
             * ---------------------------------------------------------
             * Recupero ID patente
             * ---------------------------------------------------------
             */

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


            /*
             * ---------------------------------------------------------
             * Recupero dati account
             * ---------------------------------------------------------
             */

            $username = $_POST['username'] ?? null;
            $email = $_POST['email'] ?? null;
            $password = $_POST['password'] ?? null;


            /*
             * ---------------------------------------------------------
             * Recupero dati anagrafici
             * ---------------------------------------------------------
             */

            $nome = $_POST['nome'] ?? null;
            $cognome = $_POST['cognome'] ?? null;
            $cf = $_POST['codiceFiscale'] ?? null;
            $dataNascitaParam = $_POST['dataNascita'] ?? null;
            $luogoNascita = $_POST['luogoNascita'] ?? null;
            $indirizzo = $_POST['indirizzo'] ?? null;
            $telefono = $_POST['telefono'] ?? null;


            /*
             * ---------------------------------------------------------
             * Normalizzazione dei dati
             *
             * Il controller si occupa della rappresentazione HTTP.
             * ---------------------------------------------------------
             */

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


            /*
             * ---------------------------------------------------------
             * Conversione data di nascita
             * ---------------------------------------------------------
             */

            if (
                $dataNascitaParam === null ||
                trim($dataNascitaParam) === ''
            ) {
                throw new \InvalidArgumentException(
                    "Data di nascita non valida."
                );
            }

            try {
                $dataNascita = new \DateTimeImmutable(
                    $dataNascitaParam
                );

            } catch (\Exception $e) {

                throw new \InvalidArgumentException(
                    "Data di nascita non valida."
                );
            }
            $password_hash = PasswordUtil::hashPassword($password);

            /*
             * ---------------------------------------------------------
             * BUSINESS LOGIC
             *
             * Tutta la logica dell'iscrizione viene delegata
             * al Service.
             * ---------------------------------------------------------
             */

            $iscritto = $this->service->iscrizione(
                $idPa,
                $nome,
                $cognome,
                $username,
                $email,
                $password_hash,
                $cf,
                $indirizzo,
                $luogoNascita,
                $dataNascita,
                $telefono
            );

            /*
             * ---------------------------------------------------------
             * Salvataggio definitivo
             * ---------------------------------------------------------
             */
            $this->service->confermaIscrizione(
                $iscritto
            );

            /*
             * ---------------------------------------------------------
             * LOGIN AUTOMATICO
             * ---------------------------------------------------------
             */

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['utenteLoggato'] = $iscritto;
            
            /*
             * ---------------------------------------------------------
             * REDIRECT ALLA HOME
             *
             * Per ora lasciamo il redirect relativo.
             * Lo sistemeremo definitivamente quando configureremo
             * FCFrontController e il routing.
             * ---------------------------------------------------------
             */

            header('Location: /DriveMeSafely/public/');
            exit;


        } catch (\InvalidArgumentException|\RuntimeException $e) {
            $pacchetto = null;
        
            if (isset($idPa) && $idPa > 0) {
                $pacchetto = $this->service->getPacchetto($idPa);
            }
        
            $this->view->showFormError(
                $e->getMessage(),
                $_POST,
                $pacchetto
            );
        
        } catch (\Throwable $e) {
            $this->view->showError(
                "Si è verificato un errore imprevisto durante l'iscrizione.",
                500
            );
        }
    }
}
