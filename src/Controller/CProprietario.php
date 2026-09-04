<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Entity\EProprietario;
use CamassoMedelago\DriveMeSafely\Foundation\FUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\View\VProprietario;
use Doctrine\ORM\EntityManagerInterface;

class CProprietario
{
    private FUtenteRegistrato $fUtente;
    private VProprietario $view;
    private string $contextPath;

    public function __construct(EntityManagerInterface $em, string $contextPath = '')
    {
        $this->fUtente = new FUtenteRegistrato($em);
        $this->view = new VProprietario();
        $this->contextPath = $contextPath;
    }

    public function dashboard(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = $_SESSION['utenteLoggatoId'] ?? null;
        $id = is_int($id) || (is_string($id) && ctype_digit($id)) ? (int) $id : null;
        $utente = $id === null ? null : $this->fUtente->getById($id);

        if (!$utente instanceof EProprietario) {
            header('Location: ' . $this->contextPath . '/home/login');
            exit;
        }

        $this->view->show($utente);
    }
}
