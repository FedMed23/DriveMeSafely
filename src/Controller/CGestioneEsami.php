<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Entity\EDipendente;
use CamassoMedelago\DriveMeSafely\Foundation\FUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\Service\SEffettuazioneEsami;
use CamassoMedelago\DriveMeSafely\Service\SPrenotazioneEsame;
use CamassoMedelago\DriveMeSafely\View\VGestioneEsami;
use Doctrine\ORM\EntityManagerInterface;

class CGestioneEsami
{
    public function __construct(
        private EntityManagerInterface $em,
        private string $contextPath = ''
    ) {}

    /**
     * Gestisce il caso d'uso della gestione esami da parte della segreteria.
     *
     * GET  -> visualizzazione calendario / idonei per un esame selezionato
     * POST -> elaborazione della prenotazione degli iscritti selezionati
     */
    public function gestione(): void
    {
        $this->avviaSessione();

        $id = $_SESSION['utenteLoggatoId'] ?? null;
        $utente = is_numeric($id) ? (new FUtenteRegistrato($this->em))->getById((int) $id) : null;
        if (!$utente instanceof EDipendente) {
            $this->redirect('/home/login');
        }

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->get();
                break;

            case 'POST':
                $this->post((int) $id);
                break;

            default:
                http_response_code(405);
                (new VGestioneEsami())->showError('Metodo HTTP non supportato.', 405);
                break;
        }
    }

    /**
     * Gestisce la richiesta GET: visualizza il calendario e, se selezionato,
     * l'elenco degli iscritti idonei all'esame indicato.
     */
    private function get(): void
    {
        $service = new SPrenotazioneEsame($this->em);
        $effettuazioneService = new SEffettuazioneEsami($this->em);
        $idEsame = filter_input(INPUT_GET, 'idEsame', FILTER_VALIDATE_INT);
        $calendario = $service->getCalendario();
        $successo = $_GET['successo'] ?? null;
        $errore = $_GET['errore'] ?? null;
        $idonei = [];

        if ($idEsame && $idEsame > 0) {
            try {
                $idonei = $service->getIscrittiIdonei($idEsame);
            } catch (\Throwable $e) {
                $errore = $e->getMessage();
                $idEsame = null;
            }
        }

        (new VGestioneEsami())->show(
            $calendario,
            $idonei,
            $errore,
            $successo,
            $idEsame,
            $effettuazioneService->getMappaPerPrenotazione(),
            $effettuazioneService->getIdPrenotazioniConEsameSvolto($calendario['storicoPrenotazioni'])
        );
    }

    /**
     * Gestisce la richiesta POST: elabora la prenotazione degli iscritti selezionati per l'esame.
     */
    private function post(int $idDipendente): void
    {
        $idEsame = filter_input(INPUT_POST, 'idEsame', FILTER_VALIDATE_INT);
        if (!$idEsame || $idEsame <= 0) {
            $this->redirect('/home/segreteria/gestione_esami?errore=' . rawurlencode('Selezionare una sessione d\'esame valida.'));
        }

        $rawIds = $_POST['idIscritti'] ?? [];
        if (!is_array($rawIds) || empty($rawIds)) {
            $this->redirect('/home/segreteria/gestione_esami?idEsame=' . $idEsame . '&errore=' . rawurlencode('Selezionare almeno un allievo idoneo da prenotare.'));
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $rawIds), fn($id) => $id > 0)));
        if (empty($ids)) {
            $this->redirect('/home/segreteria/gestione_esami?idEsame=' . $idEsame . '&errore=' . rawurlencode('Nessun allievo valido selezionato.'));
        }

        $service = new SPrenotazioneEsame($this->em);
        try {
            $service->prenotaEConferma($idDipendente, $idEsame, $ids);
            $this->redirect('/home/segreteria/gestione_esami?successo=prenotazione');
        } catch (\Throwable $e) {
            $this->redirect('/home/segreteria/gestione_esami?idEsame=' . $idEsame . '&errore=' . rawurlencode($e->getMessage()));
        }
    }

    /**
     * Gestisce l'annullamento di una prenotazione esame da parte della segreteria.
     */
    public function annulla(): void
    {
        $this->avviaSessione();

        $id = $_SESSION['utenteLoggatoId'] ?? null;
        $utente = is_numeric($id) ? (new FUtenteRegistrato($this->em))->getById((int) $id) : null;
        if (!$utente instanceof EDipendente) {
            $this->redirect('/home/login');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            (new VGestioneEsami())->showError('Metodo HTTP non supportato.', 405);
            return;
        }

        $idPrenotazione = filter_input(INPUT_POST, 'idPrenotazione', FILTER_VALIDATE_INT);
        if (!$idPrenotazione || $idPrenotazione <= 0) {
            $this->redirect('/home/segreteria/gestione_esami?errore=' . rawurlencode('Identificativo prenotazione non valido.'));
        }

        try {
            $service = new SPrenotazioneEsame($this->em);
            $service->annullaPrenotazione($idPrenotazione);
            $this->redirect('/home/segreteria/gestione_esami?successo=annullamento');
        } catch (\Throwable $e) {
            $this->redirect('/home/segreteria/gestione_esami?errore=' . rawurlencode($e->getMessage()));
        }
    }

    /**
     * Gestisce la registrazione dell'esito (effettuazione) di un esame già svolto.
     */
    public function registraEsito(): void
    {
        $this->verificaDipendente();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            (new VGestioneEsami())->showError('Metodo HTTP non supportato.', 405);
            return;
        }

        $idPrenotazione = filter_input(INPUT_POST, 'idPrenotazione', FILTER_VALIDATE_INT);
        $tentativi = filter_input(INPUT_POST, 'tentativi', FILTER_VALIDATE_INT);
        $superato = filter_input(INPUT_POST, 'superato', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;

        if (!$idPrenotazione || $idPrenotazione <= 0) {
            $this->redirect('/home/segreteria/gestione_esami?errore=' . rawurlencode('Identificativo prenotazione non valido.'));
        }
        if (!$tentativi || $tentativi <= 0) {
            $this->redirect('/home/segreteria/gestione_esami?errore=' . rawurlencode('Numero di tentativi non valido.'));
        }

        try {
            $service = new SEffettuazioneEsami($this->em);
            $service->registraEsito($idPrenotazione, $tentativi, $superato);
            $this->redirect('/home/segreteria/gestione_esami?successo=esito_registrato');
        } catch (\Throwable $e) {
            $this->redirect('/home/segreteria/gestione_esami?errore=' . rawurlencode($e->getMessage()));
        }
    }

    /**
     * Gestisce la modifica di un esito (effettuazione) già registrato dalla segreteria.
     */
    public function modificaEsito(): void
    {
        $this->verificaDipendente();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            (new VGestioneEsami())->showError('Metodo HTTP non supportato.', 405);
            return;
        }

        $idEffettuazione = filter_input(INPUT_POST, 'idEffettuazione', FILTER_VALIDATE_INT);
        $tentativi = filter_input(INPUT_POST, 'tentativi', FILTER_VALIDATE_INT);
        $superato = filter_input(INPUT_POST, 'superato', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;

        if (!$idEffettuazione || $idEffettuazione <= 0) {
            $this->redirect('/home/segreteria/gestione_esami?errore=' . rawurlencode('Identificativo effettuazione non valido.'));
        }
        if (!$tentativi || $tentativi <= 0) {
            $this->redirect('/home/segreteria/gestione_esami?errore=' . rawurlencode('Numero di tentativi non valido.'));
        }

        try {
            $service = new SEffettuazioneEsami($this->em);
            $service->modificaEsito($idEffettuazione, $tentativi, $superato);
            $this->redirect('/home/segreteria/gestione_esami?successo=esito_modificato');
        } catch (\Throwable $e) {
            $this->redirect('/home/segreteria/gestione_esami?errore=' . rawurlencode($e->getMessage()));
        }
    }

    /**
     * Gestisce l'annullamento di un esito già registrato: elimina l'effettuazione
     * e ripristina la prenotazione allo stato PRENOTATO.
     */
    public function annullaEsito(): void
    {
        $this->verificaDipendente();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            (new VGestioneEsami())->showError('Metodo HTTP non supportato.', 405);
            return;
        }

        $idEffettuazione = filter_input(INPUT_POST, 'idEffettuazione', FILTER_VALIDATE_INT);
        if (!$idEffettuazione || $idEffettuazione <= 0) {
            $this->redirect('/home/segreteria/gestione_esami?errore=' . rawurlencode('Identificativo effettuazione non valido.'));
        }

        try {
            $service = new SEffettuazioneEsami($this->em);
            $service->annullaEsito($idEffettuazione);
            $this->redirect('/home/segreteria/gestione_esami?successo=esito_annullato');
        } catch (\Throwable $e) {
            $this->redirect('/home/segreteria/gestione_esami?errore=' . rawurlencode($e->getMessage()));
        }
    }

    /**
     * Verifica che l'utente in sessione sia un dipendente autorizzato; in caso contrario reindirizza al login.
     */
    private function verificaDipendente(): void
    {
        $this->avviaSessione();
        $id = $_SESSION['utenteLoggatoId'] ?? null;
        $utente = is_numeric($id) ? (new FUtenteRegistrato($this->em))->getById((int) $id) : null;
        if (!$utente instanceof EDipendente) {
            $this->redirect('/home/login');
        }
    }

    private function avviaSessione(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    private function redirect(string $path): never
    {
        header('Location: ' . $this->contextPath . $path);
        exit;
    }
}
