<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Entity\EDipendente;
use CamassoMedelago\DriveMeSafely\Entity\EProprietario;
use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\Service\SQuiz;
use CamassoMedelago\DriveMeSafely\View\VQuiz;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Controller responsabile della visualizzazione dei quiz (lista ed esito).
 *
 * L'elaborazione dello svolgimento vero e proprio (generazione e correzione
 * delle domande) è demandata a CSvolgimentoQuiz, tenuta separata perché più
 * onerosa in termini di logica applicativa.
 */
class CQuiz
{
    private SQuiz $service;
    private FUtenteRegistrato $fUtente;
    private VQuiz $view;
    private string $contextPath;

    public function __construct(EntityManagerInterface $em, string $contextPath = '')
    {
        $this->service = new SQuiz($em);
        $this->fUtente = new FUtenteRegistrato($em);
        $this->view = new VQuiz();
        $this->contextPath = $contextPath;
    }

    /**
     * Gestisce il caso d'uso della lista quiz e delle statistiche personali dell'allievo.
     *
     * GET  -> visualizzazione della lista quiz disponibili con le statistiche
     * POST -> non previsto, restituisce 405
     */
    public function quiz(): void
    {
        $this->avviaSessione();
        $utente = $this->utenteIscritto();

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->getQuiz($utente);
                break;

            default:
                http_response_code(405);
                $this->view->showError('Metodo HTTP non supportato.', 405);
                break;
        }
    }

    /**
     * Gestisce la richiesta GET: visualizza la lista dei quiz e le statistiche dell'allievo.
     */
    private function getQuiz(EIscritto $utente): void
    {
        $this->view->showLista(
            $this->service->getQuiz(),
            $utente,
            $this->service->getStatisticheAllievo($utente)
        );
    }

    /**
     * Gestisce il caso d'uso della visualizzazione dell'esito dell'ultimo quiz svolto.
     *
     * GET  -> visualizzazione dell'esito recuperato dalla sessione
     * POST -> non previsto, restituisce 405
     */
    public function esito(): void
    {
        $this->avviaSessione();
        $utente = $this->utenteIscritto();

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->getEsito($utente);
                break;

            default:
                http_response_code(405);
                $this->view->showError('Metodo HTTP non supportato.', 405);
                break;
        }
    }

    /**
     * Gestisce la richiesta GET: recupera dalla sessione l'ultimo svolgimento e ne mostra l'esito.
     */
    private function getEsito(EIscritto $utente): void
    {
        $id = $_SESSION['ultimoSvolgimentoQuiz'] ?? null;
        if (!is_int($id) && !(is_string($id) && ctype_digit($id))) {
            $this->redirect('/home/quiz');
        }
        try {
            $this->view->showEsitoFromId(
                $this->service,
                (int) $id,
                (int) $utente->getId()
            );
            unset($_SESSION['ultimoSvolgimentoQuiz']);
        } catch (\InvalidArgumentException $e) {
            $this->view->showError($e->getMessage(), 404);
        }
    }

    private function utenteIscritto(): EIscritto
    {
        $id = $_SESSION['utenteLoggatoId'] ?? null;
        if (!is_int($id) && !(is_string($id) && ctype_digit($id))) {
            $this->redirect('/home/login');
        }

        $utente = $this->fUtente->getById((int) $id);
        if ($utente === null) {
            $this->redirect('/home/login');
        }

        if ($utente instanceof EDipendente) {
            $this->redirect('/home/segreteria');
        }

        if ($utente instanceof EProprietario) {
            $this->redirect('/home/proprietario');
        }

        if (!$utente instanceof EIscritto) {
            $this->redirect('/home/login');
        }

        return $utente;
    }

    private function avviaSessione(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function redirect(string $path): never
    {
        header('Location: ' . $this->contextPath . $path);
        exit;
    }
}
