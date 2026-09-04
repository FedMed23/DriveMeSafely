<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Service\SProfilo;
use CamassoMedelago\DriveMeSafely\View\VProfilo;
use Doctrine\ORM\EntityManagerInterface;

class CProfilo
{
    private SProfilo $service;
    private VProfilo $view;
    private string $contextPath;

    public function __construct(EntityManagerInterface $em, string $contextPath = '')
    {
        $this->service = new SProfilo($em);
        $this->view = new VProfilo();
        $this->contextPath = $contextPath;
    }

    /**
     * Gestisce il caso d'uso della visualizzazione del profilo dell'utente registrato.
     *
     * GET -> visualizzazione dei dati anagrafici e di iscrizione (sola lettura)
     */
    public function profilo(): void
    {
        $this->sessione();
        $id = $this->idSessione();
        if ($id === null) {
            $this->redirect('/home/login');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            $this->view->showError('Metodo HTTP non supportato.', 405);
            return;
        }

        try {
            $iscritto = $this->service->getProfilo($id);
            $this->view->showProfilo($iscritto);
        } catch (\InvalidArgumentException $e) {
            $this->redirect('/home/login');
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
