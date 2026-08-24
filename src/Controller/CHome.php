<?php
namespace CamassoMedelago\DriveMeSafely\Controller;
use CamassoMedelago\DriveMeSafely\View\VHome;
class CHome
{
    private VHome $view;
    public function __construct(VHome $view)
    {
        $this->view = $view;
    }
    public function home(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $utenteLoggato = $_SESSION['utenteLoggato'] ?? null;
        $this->view->showHome($utenteLoggato);
    }
}