<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Foundation\FUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\Service\SPagamentoSpese;
use CamassoMedelago\DriveMeSafely\View\VMieSpese;
use Doctrine\ORM\EntityManagerInterface;

class CMieSpese
{
    private SPagamentoSpese $service;
    private FUtenteRegistrato $fUtente;
    private VMieSpese $view;

    public function __construct(EntityManagerInterface $em)
    {
        $this->service = new SPagamentoSpese($em);
        $this->fUtente = new FUtenteRegistrato($em);
        $this->view = new VMieSpese();
    }

    public function mieSpese(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $utenteId = $_SESSION['utenteLoggatoId'] ?? null;
        if (!is_int($utenteId) && !(is_string($utenteId) && ctype_digit($utenteId))) {
            $this->redirect('/home/login');
        }

        try {
            $report = $this->service->getSpese((int) $utenteId);
        } catch (\InvalidArgumentException $e) {
            $this->view->showError($e->getMessage(), 400);
            return;
        }

        $utente = $this->fUtente->getById((int) $utenteId);
        if ($utente === null) {
            $this->view->showError('Utente non trovato.', 404);
            return;
        }

        $this->view->showSpese(
            $report,
            $utente,
            ($_GET['successo'] ?? null) === 'true',
            isset($_GET['errore']) ? (string) $_GET['errore'] : null
        );
    }

    private function redirect(string $path): never
    {
        header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . $path);
        exit;
    }
}
