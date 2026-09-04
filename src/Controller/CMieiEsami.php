<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Service\SMieiEsami;
use CamassoMedelago\DriveMeSafely\View\VMieiEsami;
use Doctrine\ORM\EntityManagerInterface;

class CMieiEsami
{
    private SMieiEsami $service;
    private FIscritto $fIscritto;
    private VMieiEsami $view;
    private string $contextPath;

    public function __construct(EntityManagerInterface $em, string $contextPath = '')
    {
        $this->service = new SMieiEsami($em);
        $this->fIscritto = new FIscritto($em);
        $this->view = new VMieiEsami();
        $this->contextPath = $contextPath;
    }

    /**
     * Gestisce il caso d'uso della consultazione delle proprie prenotazioni d'esame.
     *
     * GET -> visualizzazione dello storico prenotazioni ed esiti (sola lettura)
     */
    public function miei(): void
    {
        $this->sessione();
        $id = $this->idSessione();
        if ($id === null || $this->fIscritto->findById($id) === null) {
            $this->redirect('/home/login');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            $this->view->showError('Metodo HTTP non supportato.', 405);
            return;
        }

        $storico = $this->service->getStoricoEsami($id);
        $this->view->showStorico(
            $storico['prenotazioni'],
            $storico['effettuazioniPerPrenotazione'],
            $storico['eventiCalendario']
        );
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
