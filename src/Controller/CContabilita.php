<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Service\SPagamentoSpese;
use CamassoMedelago\DriveMeSafely\View\VContabilita;
use Doctrine\ORM\EntityManagerInterface;

class CContabilita
{
    private SPagamentoSpese $service;
    private VContabilita $view;

    public function __construct(EntityManagerInterface $em)
    {
        $this->service = new SPagamentoSpese($em);
        $this->view = new VContabilita();
    }

    public function contabilita(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $utenteId = $_SESSION['utenteLoggatoId'] ?? null;
        if (!is_int($utenteId) && !(is_string($utenteId) && ctype_digit($utenteId))) {
            $this->redirect('/home/login');
        }

        try {
            $cassa = $this->service->getMovimentiCassa((int) $utenteId);
        } catch (\InvalidArgumentException $e) {
            $this->view->showError($e->getMessage(), 403);
            return;
        }

        $this->view->showCassa($cassa);
    }

    private function redirect(string $path): never
    {
        header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . $path);
        exit;
    }
}
