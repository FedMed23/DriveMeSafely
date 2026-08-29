<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Service\SLogin;
use CamassoMedelago\DriveMeSafely\View\VLogin;
use CamassoMedelago\DriveMeSafely\Entity\EDipendente;
use CamassoMedelago\DriveMeSafely\Entity\EProprietario;
use Doctrine\ORM\EntityManagerInterface;

class CLogin
{
    private SLogin $service;
    private VLogin $view;

    public function __construct(EntityManagerInterface $em)
    {
        $this->service = new SLogin($em);
        $this->view = new VLogin();
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->showForm();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->view->showError('Metodo HTTP non supportato.', 405);
            return;
        }

        $session = $this->getSession();
        $captcha = filter_input(INPUT_POST, 'captcha', FILTER_VALIDATE_INT);
        $expectedCaptcha = $_SESSION['rispostaEsattaCaptcha'] ?? null;

        if ($captcha === false || $expectedCaptcha === null
            || $captcha !== $expectedCaptcha) {
            $this->showForm(
                'Risposta al controllo di sicurezza errata. Riprova.',
                $_POST,
                $session
            );
            return;
        }

        try {
            $utente = $this->service->autentica(
                (string) ($_POST['username'] ?? ''),
                (string) ($_POST['password'] ?? '')
            );

            session_regenerate_id(true);
            $_SESSION['utenteLoggatoId'] = $utente->getId();
            unset($_SESSION['rispostaEsattaCaptcha']);

            $destinazione = match (true) {
                $utente instanceof EDipendente => '/home/segreteria',
                $utente instanceof EProprietario => '/home/proprietario',
                default => '/home',
            };
            $_SESSION['homeUrl'] = $destinazione;
            header('Location: ' . $this->contextPath() . $destinazione);
            exit;
        } catch (\InvalidArgumentException $e) {
            $this->showForm($e->getMessage(), $_POST, $session);
        }
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();

        header('Location: ' . $this->contextPath() . '/');
        exit;
    }

    private function showForm(
        ?string $errore = null,
        array $oldData = [],
        ?bool $sessionStarted = null
    ): void {
        if ($sessionStarted === null) {
            $this->getSession();
        }

        $num1 = random_int(1, 10);
        $num2 = random_int(1, 10);
        $_SESSION['rispostaEsattaCaptcha'] = $num1 + $num2;
        $this->view->showForm($num1, $num2, $errore, $oldData);
    }

    private function getSession(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return true;
    }

    private function contextPath(): string
    {
        return rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    }
}
