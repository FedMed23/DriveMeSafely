<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Entity\EDipendente;
use CamassoMedelago\DriveMeSafely\Foundation\FUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\Service\SIdoneitaEsame;
use CamassoMedelago\DriveMeSafely\View\VIdoneitaEsami;
use Doctrine\ORM\EntityManagerInterface;

class CIdoneitaEsami
{
    public function __construct(
        private EntityManagerInterface $em,
        private string $contextPath = ''
    ) {}

    /**
     * Gestisce il caso d'uso della visualizzazione dell'idoneità agli esami da parte della segreteria.
     *
     * GET -> visualizzazione dei profili quiz e dell'idoneità di ciascun iscritto
     */
    public function idoneita(): void
    {
        $this->avviaSessione();

        $id = $_SESSION['utenteLoggatoId'] ?? null;
        $utente = is_numeric($id) ? (new FUtenteRegistrato($this->em))->getById((int) $id) : null;
        if (!$utente instanceof EDipendente) {
            $this->redirect('/home/login');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            (new VIdoneitaEsami())->showError('Metodo HTTP non supportato.', 405);
            return;
        }

        $service = new SIdoneitaEsame($this->em);
        (new VIdoneitaEsami())->show($service->getProfiliQuizIscritti());
    }

    private function avviaSessione(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    private function redirect(string $path): never
    {
        header('Location: ' . $this->contextPath . $path);
        exit;
    }
}
