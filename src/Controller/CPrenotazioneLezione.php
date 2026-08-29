<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Service\SPrenotazioneLezione;
use CamassoMedelago\DriveMeSafely\View\VPrenotazioneLezione;
use Doctrine\ORM\EntityManagerInterface;

class CPrenotazioneLezione
{
    private SPrenotazioneLezione $service;
    private FIscritto $fIscritto;
    private VPrenotazioneLezione $view;

    public function __construct(EntityManagerInterface $em)
    {
        $this->service = new SPrenotazioneLezione($em);
        $this->fIscritto = new FIscritto($em);
        $this->view = new VPrenotazioneLezione();
    }

    public function prenotazioni(): void
    {
        $this->sessione();
        $id = $this->idSessione();
        if ($id === null || $this->fIscritto->findById($id) === null) {
            $this->redirect('/home/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lezione = filter_input(INPUT_POST, 'lezione', FILTER_VALIDATE_INT);
            if ($lezione === false || $lezione === null) {
                $this->redirect('/home/prenotazioni?errore=Lezione+non+valida');
            }
            try {
                $tipo = (string) ($_POST['tipoLezione'] ?? '');
                $prenotazione = $tipo === 'TEORIA'
                    ? $this->service->prenotaTeoria($id, $lezione)
                    : $this->service->prenotaGuida($id, $lezione);
                $this->service->conferma($prenotazione);
                $this->redirect('/home/prenotazioni?successo=true');
            } catch (\InvalidArgumentException $e) {
                $this->redirect('/home/prenotazioni?errore=' . rawurlencode($e->getMessage()));
            }
        }

        $calendario = $this->service->getCalendarioAllievo($id);
        $this->view->showCalendario(
            $calendario['storicoPrenotazioni'],
            $calendario['lezioniDisponibili'],
            isset($_GET['successo']),
            isset($_GET['errore']) ? (string) $_GET['errore'] : null
        );
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
        header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . $path);
        exit;
    }
}
