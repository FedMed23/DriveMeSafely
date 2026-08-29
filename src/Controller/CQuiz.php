<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Service\SQuiz;
use CamassoMedelago\DriveMeSafely\View\VQuiz;
use Doctrine\ORM\EntityManagerInterface;

class CQuiz
{
    private SQuiz $service;
    private FIscritto $fIscritto;
    private VQuiz $view;

    public function __construct(EntityManagerInterface $em)
    {
        $this->service = new SQuiz($em);
        $this->fIscritto = new FIscritto($em);
        $this->view = new VQuiz();
    }

    public function quiz(): void
    {
        $this->avviaSessione();
        $utente = $this->utenteIscritto();
        if ($utente === null) {
            $this->redirect('/home/login');
        }
        $this->view->showLista($this->service->getQuiz(), $utente);
    }

    public function svolgimento(): void
    {
        $this->avviaSessione();
        $utente = $this->utenteIscritto();
        if ($utente === null) {
            $this->redirect('/home/login');
        }

        $idQuiz = filter_input(
            $_SERVER['REQUEST_METHOD'] === 'POST' ? INPUT_POST : INPUT_GET,
            'idQuiz',
            FILTER_VALIDATE_INT
        );
        if ($idQuiz === false || $idQuiz === null) {
            $this->redirect('/home/quiz');
        }

        try {
            $quiz = $this->service->getQuizById($idQuiz);
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $this->view->showSvolgimento(
                    $quiz,
                    $this->service->generaQuiz($idQuiz, $utente)
                );
                return;
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->view->showError('Metodo HTTP non supportato.', 405);
                return;
            }

            $risposte = [];
            foreach ($_POST as $nome => $valore) {
                if (str_starts_with((string) $nome, 'risposta_')) {
                    $risposte[substr((string) $nome, 9)] = $valore;
                }
            }
            $svolgimento = $this->service->correggiQuiz($idQuiz, $utente, $risposte);
            $this->service->confermaSvolgimento($svolgimento);
            $_SESSION['ultimoSvolgimentoQuiz'] = $svolgimento->getIdSvolgimento();
            $this->redirect('/home/quiz/esito');
        } catch (\InvalidArgumentException $e) {
            $this->view->showError($e->getMessage(), 400);
        }
    }

    public function esito(): void
    {
        $this->avviaSessione();
        $utente = $this->utenteIscritto();
        if ($utente === null) {
            $this->redirect('/home/login');
        }
        $id = $_SESSION['ultimoSvolgimentoQuiz'] ?? null;
        if (!is_int($id) && !(is_string($id) && ctype_digit($id))) {
            $this->redirect('/home/quiz');
        }
        try {
            $this->view->showEsitoFromId(
                $this->service,
                (int) $id,
                (int) $utente->getId()
            );
            unset($_SESSION['ultimoSvolgimentoQuiz']);
        } catch (\InvalidArgumentException $e) {
            $this->view->showError($e->getMessage(), 404);
        }
    }

    private function utenteIscritto(): ?EIscritto
    {
        $id = $_SESSION['utenteLoggatoId'] ?? null;
        if (!is_int($id) && !(is_string($id) && ctype_digit($id))) {
            return null;
        }
        return $this->fIscritto->findById((int) $id);
    }

    private function avviaSessione(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function redirect(string $path): never
    {
        header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . $path);
        exit;
    }
}
