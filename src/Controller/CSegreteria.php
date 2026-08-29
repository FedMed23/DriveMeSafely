<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Entity\EDipendente;
use CamassoMedelago\DriveMeSafely\Foundation\FUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\View\VSegreteria;
use Doctrine\ORM\EntityManagerInterface;

class CSegreteria
{
    public function __construct(
        private EntityManagerInterface $em,
        private ?FUtenteRegistrato $fUtente = null,
        private ?VSegreteria $view = null
    ) {
        $this->fUtente ??= new FUtenteRegistrato($em);
        $this->view ??= new VSegreteria();
    }

    public function home(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = $_SESSION['utenteLoggatoId'] ?? null;
        $id = is_int($id) || (is_string($id) && ctype_digit($id)) ? (int) $id : null;
        $utente = $id === null ? null : $this->fUtente->getById($id);

        if (!$utente instanceof EDipendente) {
            header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/home/login');
            exit;
        }

        $this->view->show($utente);
    }
}
