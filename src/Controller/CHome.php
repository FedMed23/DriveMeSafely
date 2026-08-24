<?php
namespace CamassoMedelago\DriveMeSafely\Controller;

use Doctrine\ORM\EntityManagerInterface;
use CamassoMedelago\DriveMeSafely\View\VHome;

class CHome
{
    private VHome $view;

    /**
     * Il costruttore accetta l'EntityManagerInterface per allineamento con il Front Controller,
     * e inizializza la View in totale autonomia.
     */
    public function __construct(EntityManagerInterface $em)
    {
        // Anche se non usiamo $em qui dentro, serve riceverlo per non far crashare il Front Controller!
        $this->view = new VHome();
    }

    /**
     * Punto di ingresso principale gestito dal Front Controller (rotta /home)
     */
    public function home(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Recupera l'oggetto allievo dalla sessione se presente
        $utenteLoggato = $_SESSION['utenteLoggato'] ?? null;

        // Invia i dati alla View per il rendering su Smarty
        $this->view->showHome($utenteLoggato);
    }
}
