<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Foundation\FUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\Service\SPagamentoSpese;
use CamassoMedelago\DriveMeSafely\View\VPagamentoSpese;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class CPagamentoSpese
{
    private SPagamentoSpese $service;
    private VPagamentoSpese $view;
    private FUtenteRegistrato $fUtente;

    public function __construct(EntityManagerInterface $em)
    {
        $this->service = new SPagamentoSpese($em);
        $this->fUtente = new FUtenteRegistrato($em);
        $this->view = new VPagamentoSpese();
    }

    public function pagamento(): void
    {
        $this->avviaSessione();

        $utenteId = $this->utenteIdInSessione();
        if ($utenteId === null) {
            $this->redirect('/home/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->mostraForm($utenteId);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->view->showError('Metodo HTTP non supportato.', 405);
            return;
        }

        $idSpesa = filter_input(INPUT_POST, 'idSpesa', FILTER_VALIDATE_INT);
        $numeroCarta = trim((string) ($_POST['numeroCarta'] ?? ''));
        $numeroCartaNormalizzato = preg_replace('/[\s-]+/', '', $numeroCarta);
        if ($numeroCartaNormalizzato === null) {
            $numeroCartaNormalizzato = '';
        }
        $nomeTitolare = trim((string) ($_POST['nomeTitolare'] ?? ''));
        $cognomeTitolare = trim((string) ($_POST['cognomeTitolare'] ?? ''));
        $dataScadenza = trim((string) ($_POST['dataScadenza'] ?? ''));

        if ($idSpesa === false || $idSpesa === null || $dataScadenza === '') {
            $this->mostraErroreForm(
                'Parametri del pagamento non validi.',
                $idSpesa,
                $_POST
            );
            return;
        }

        try {
            $scadenza = new DateTimeImmutable($dataScadenza);
        } catch (\Exception) {
            $this->mostraErroreForm(
                'La data di scadenza non è valida.',
                $idSpesa,
                $_POST
            );
            return;
        }

        try {
            $pagamento = $this->service->pagaSpesa(
                $utenteId,
                $idSpesa,
                $numeroCartaNormalizzato,
                $nomeTitolare,
                $cognomeTitolare,
                $scadenza
            );
            $this->service->confermaPagamento($pagamento);
        } catch (\InvalidArgumentException $e) {
            $this->mostraErroreForm($e->getMessage(), $idSpesa, $_POST);
            return;
        }

        $this->redirect('/home/mie_spese?successo=true');
    }

    private function mostraForm(int $utenteId): void
    {
        $idSpesa = filter_input(INPUT_GET, 'idSpesa', FILTER_VALIDATE_INT);
        if ($idSpesa === false || $idSpesa === null) {
            $this->redirect('/home/mie_spese?errore=Spesa+non+valida');
        }

        try {
            $spesa = $this->service->getSpesaPerUtente($utenteId, $idSpesa);
        } catch (\InvalidArgumentException $e) {
            $this->redirect('/home/mie_spese?errore=' . rawurlencode($e->getMessage()));
        }

        $utente = $this->fUtente->getById($utenteId);
        $this->view->showForm($spesa, null, [], $utente);
    }

    private function mostraErroreForm(
        string $messaggio,
        ?int $idSpesa,
        array $oldData
    ): void {
        if ($idSpesa === null) {
            $this->redirect('/home/mie_spese?errore=' . rawurlencode($messaggio));
        }

        try {
            $spesa = $this->service->getSpesa($idSpesa);
        } catch (\InvalidArgumentException) {
            $this->redirect('/home/mie_spese?errore=Spesa+non+trovata');
        }

        $utente = $this->fUtente->getById($this->utenteIdInSessione() ?? -1);
        $this->view->showForm($spesa, $messaggio, $oldData, $utente);
    }

    private function avviaSessione(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function utenteIdInSessione(): ?int
    {
        $utenteId = $_SESSION['utenteLoggatoId'] ?? null;
        if (is_int($utenteId)) {
            return $utenteId;
        }

        if (is_string($utenteId) && ctype_digit($utenteId)) {
            return (int) $utenteId;
        }

        return null;
    }

    private function redirect(string $path): never
    {
        header('Location: ' . $this->contextPath() . $path);
        exit;
    }

    private function contextPath(): string
    {
        return rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    }
}
