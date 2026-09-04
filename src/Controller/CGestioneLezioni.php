<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Entity\EDipendente;
use CamassoMedelago\DriveMeSafely\Foundation\FUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\Service\SLezione;
use CamassoMedelago\DriveMeSafely\View\VGesioneLezioni;
use Doctrine\ORM\EntityManagerInterface;

class CGestioneLezioni
{
    private SLezione $service;
    private FUtenteRegistrato $fUtente;
    private VGesioneLezioni $view;
    private string $contextPath;

    public function __construct(EntityManagerInterface $em, string $contextPath = '')
    {
        $this->service = new SLezione($em);
        $this->fUtente = new FUtenteRegistrato($em);
        $this->view = new VGesioneLezioni();
        $this->contextPath = $contextPath;
    }

    /**
     * Gestisce il caso d'uso della gestione lezioni da parte della segreteria.
     *
     * GET  -> visualizzazione palinsesto lezioni
     * POST -> inserimento o annullamento di una lezione
     */
    public function gestioneLezioni(): void
    {
        $this->sessione();
        $id = $this->idSessione();
        $utente = $id === null ? null : $this->fUtente->getById($id);
        if (!$utente instanceof EDipendente) {
            $this->redirect('/home/login');
        }

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->get();
                break;

            case 'POST':
                $this->post();
                break;

            default:
                http_response_code(405);
                $this->view->showError('Metodo HTTP non supportato.', 405);
                break;
        }
    }

    /**
     * Gestisce la richiesta GET: visualizza il palinsesto delle lezioni e i suggerimenti per il form.
     */
    private function get(): void
    {
        $this->view->show(
            $this->service->getPalinsesto(),
            $_GET['successo'] ?? null,
            $_GET['errore'] ?? null,
            $this->service->getIstruttoriSuggeriti(),
            $this->service->getVettureSuggerite()
        );
    }

    /**
     * Gestisce la richiesta POST: inserisce o annulla una lezione a seconda dell'azione ricevuta.
     */
    private function post(): void
    {
        try {
            $azione = $_POST['azione'] ?? 'inserisci';
            if ($azione === 'annulla') {
                $idLezione = filter_input(INPUT_POST, 'idLezione', FILTER_VALIDATE_INT);
                if ($idLezione === false || $idLezione === null) {
                    throw new \InvalidArgumentException('Lezione non valida.');
                }
                $this->service->annullaLezione($idLezione);
                $messaggio = 'Lezione e relative prenotazioni annullate.';
            } else {
                $lezione = $this->service->inserisciLezione($_POST);
                $this->service->confermaLezione($lezione);
                $messaggio = 'Lezione inserita.';
            }
            $this->redirect('/home/segreteria/gestione_lezioni?successo=' . rawurlencode($messaggio));
        } catch (\Throwable $e) {
            $this->redirect('/home/segreteria/gestione_lezioni?errore=' . rawurlencode($e->getMessage()));
        }
    }

    private function sessione(): void { if (session_status() === PHP_SESSION_NONE) session_start(); }
    private function idSessione(): ?int { $id=$_SESSION['utenteLoggatoId']??null; return is_numeric($id)?(int)$id:null; }
    private function redirect(string $path): never { header('Location: '.$this->contextPath.$path); exit; }
}
