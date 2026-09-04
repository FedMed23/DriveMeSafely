<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Service\SPrenotazioneLezione;
use CamassoMedelago\DriveMeSafely\View\VPrenotazioneLezione;
use Doctrine\ORM\EntityManagerInterface;

class CPrenotazioneLezione
{
    private SPrenotazioneLezione $service;
    private FIscritto $fIscritto;
    private VPrenotazioneLezione $view;
    private string $contextPath;

    public function __construct(EntityManagerInterface $em, string $contextPath = '')
    {
        $this->service = new SPrenotazioneLezione($em);
        $this->fIscritto = new FIscritto($em);
        $this->view = new VPrenotazioneLezione();
        $this->contextPath = $contextPath;
    }

    /**
     * Gestisce il caso d'uso della prenotazione lezioni.
     *
     * GET  -> visualizzazione calendario prenotazioni/lezioni disponibili
     * POST -> elaborazione della prenotazione di una lezione
     */
    public function prenotazioni(): void
    {
        $this->sessione();
        $id = $this->idSessione();
        if ($id === null || $this->fIscritto->findById($id) === null) {
            $this->redirect('/home/login');
        }

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->getPrenotazioni($id);
                break;

            case 'POST':
                $this->postPrenotazioni($id);
                break;

            default:
                http_response_code(405);
                $this->view->showError('Metodo HTTP non supportato.', 405);
                break;
        }
    }

    /**
     * Gestisce la richiesta GET: visualizza lo storico prenotazioni e le lezioni disponibili.
     */
    private function getPrenotazioni(int $id): void
    {
        $calendario = $this->service->getCalendarioAllievo($id);
        $this->view->showCalendario(
            $calendario['storicoPrenotazioni'],
            $calendario['lezioniDisponibili'],
            isset($_GET['successo']),
            isset($_GET['errore']) ? (string) $_GET['errore'] : null
        );
    }

    /**
     * Gestisce la richiesta POST: prenota la lezione selezionata per l'allievo in sessione.
     */
    private function postPrenotazioni(int $id): void
    {
        $lezione = filter_input(INPUT_POST, 'lezione', FILTER_VALIDATE_INT);
        $tipoLezione = isset($_POST['tipoLezione']) && is_string($_POST['tipoLezione']) ? trim($_POST['tipoLezione']) : null;

        if ($lezione === false || $lezione === null || $lezione <= 0) {
            $this->redirect('/home/prenotazioni?errore=Lezione+non+valida');
        }
        try {
            $this->service->prenotaLezione($id, $lezione, $tipoLezione);
            $this->redirect('/home/prenotazioni?successo=true');
        } catch (\InvalidArgumentException $e) {
            $this->redirect('/home/prenotazioni?errore=' . rawurlencode($e->getMessage()));
        }
    }

    /**
     * Gestisce il caso d'uso dell'annullamento di una prenotazione.
     *
     * GET  -> non previsto, reindirizza alla pagina delle prenotazioni
     * POST -> elaborazione dell'annullamento della prenotazione
     */
    public function annulla(): void
    {
        $this->sessione();
        $id = $this->idSessione();
        if ($id === null || $this->fIscritto->findById($id) === null) {
            $this->redirect('/home/login');
        }

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->redirect('/home/prenotazioni');
                break;

            case 'POST':
                $this->postAnnulla($id);
                break;

            default:
                http_response_code(405);
                $this->view->showError('Metodo HTTP non supportato.', 405);
                break;
        }
    }

    /**
     * Gestisce la richiesta POST: annulla la prenotazione indicata.
     */
    private function postAnnulla(int $idIscritto): void
    {
        $idPrenotazione = filter_input(INPUT_POST, 'prenotazione', FILTER_VALIDATE_INT);
        if ($idPrenotazione === false || $idPrenotazione === null) {
            $this->redirect('/home/prenotazioni?errore=Prenotazione+non+valida');
        }
        try {
            $this->service->annullaPrenotazione($idPrenotazione, $idIscritto);
            $this->redirect('/home/prenotazioni?successo=Prenotazione+annullata');
        } catch (\InvalidArgumentException $e) {
            $this->redirect('/home/prenotazioni?errore=' . rawurlencode($e->getMessage()));
        }
    }

    private function sessione(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function idSessione(): ?int
    {
        $id = $_SESSION['utenteLoggatoId'] ?? null;
        return is_int($id) ? $id : (is_string($id) && ctype_digit($id) ? (int) $id : null);
    }

    private function redirect(string $path): never
    {
        header('Location: ' . $this->contextPath . $path);
        exit;
    }
}
