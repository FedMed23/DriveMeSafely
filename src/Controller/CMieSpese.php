<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Entity\EDipendente;
use CamassoMedelago\DriveMeSafely\Foundation\FUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\Service\SPagamentoSpese;
use CamassoMedelago\DriveMeSafely\View\VMieSpese;
use Doctrine\ORM\EntityManagerInterface;

class CMieSpese
{
    private SPagamentoSpese $service;
    private FUtenteRegistrato $fUtente;
    private VMieSpese $view;
    private string $contextPath;

    public function __construct(EntityManagerInterface $em, string $contextPath = '')
    {
        $this->service = new SPagamentoSpese($em);
        $this->fUtente = new FUtenteRegistrato($em);
        $this->view = new VMieSpese();
        $this->contextPath = $contextPath;
    }

    public function mieSpese(): void
    {
        //Avvio sessione e controllo utente loggato
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $utenteId = $_SESSION['utenteLoggatoId'] ?? null;
        if (!is_int($utenteId) && !(is_string($utenteId) && ctype_digit($utenteId))) {
            $this->redirect('/home/login');
        }

        $utente = $this->fUtente->getById((int) $utenteId);
        if ($utente === null) {
            $this->view->showError('Utente non trovato.', 404);
            return;
        }

        // I dipendenti della segreteria non hanno un prospetto spese personale
        if ($utente instanceof EDipendente) {
            $this->redirect('/home/segreteria');
        }

        //Recupera dal service le spese in base all'utente
        try {
            $report = $this->service->getSpese((int) $utenteId);
        } catch (\InvalidArgumentException $e) {
            $this->view->showError($e->getMessage(), 400);
            return;
        }

        //Passaggio dati alla view
        $this->view->showSpese(
            $report,
            $utente,
            ($_GET['successo'] ?? null) === 'true',
            isset($_GET['errore']) ? (string) $_GET['errore'] : null
        );
    }

    private function redirect(string $path): never
    {
        header('Location: ' . $this->contextPath . $path);
        exit;
    }
}
