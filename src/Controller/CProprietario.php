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

    public function __construct(EntityManagerInterface $em)
    {
        $this->fUtente = new FUtenteRegistrato($em);
        $this->view = new VProprietario();
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
            header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/home/login');
            exit;
        }

        $this->view->show($utente);
    }
}
