<?php
namespace CamassoMedelago\DriveMeSafely\Controller;

use Doctrine\ORM\EntityManagerInterface;
use CamassoMedelago\DriveMeSafely\View\VHome;
use CamassoMedelago\DriveMeSafely\Foundation\FUtenteRegistrato;

class CHome
{
    private VHome $view;
    private FUtenteRegistrato $fUtente;

    /**
     * Il costruttore accetta l'EntityManagerInterface per allineamento con il Front Controller,
     * e inizializza la View in totale autonomia.
     */
    public function __construct(EntityManagerInterface $em, string $contextPath = '')
    {
        // Anche se non usiamo $em/$contextPath qui dentro, servono per non far crashare il Front Controller!
        $this->view = new VHome();
        $this->fUtente = new FUtenteRegistrato($em);
    }

    /**
     * Punto di ingresso principale gestito dal Front Controller (rotta /home)
     */
    public function home(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $utenteLoggato = null;
        $utenteId = $_SESSION['utenteLoggatoId'] ?? null;
        if (is_int($utenteId) || (is_string($utenteId) && ctype_digit($utenteId))) {
            $utenteLoggato = $this->fUtente->getById((int) $utenteId);
        }

        // Invia i dati alla View per il rendering su Smarty
        $this->view->showHome($utenteLoggato);
    }
}
