<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Entity\EDipendente;
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
    private string $contextPath;

    public function __construct(EntityManagerInterface $em, string $contextPath = '')
    {
        $this->service = new SPagamentoSpese($em);
        $this->fUtente = new FUtenteRegistrato($em);
        $this->view = new VPagamentoSpese();
        $this->contextPath = $contextPath;
    }

    /**
     * Gestisce il caso d'uso del pagamento di una spesa.
     *
     * GET  -> visualizzazione del form di pagamento
     * POST -> elaborazione del pagamento inviato
     */
    public function pagamento(): void
    {
        $this->avviaSessione();

        $utenteId = $this->utenteIdInSessione();
        if ($utenteId === null) {
            $this->redirect('/home/login');
        }

        $utente = $this->fUtente->getById($utenteId);
        if ($utente === null) {
            $this->redirect('/home/login');
        }

        if ($utente instanceof EDipendente) {
            $this->redirect('/home/segreteria');
        }

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->get($utenteId);
                break;

            case 'POST':
                $this->post($utenteId);
                break;

            default:
                http_response_code(405);
                $this->view->showError('Metodo HTTP non supportato.', 405);
                break;
        }
    }

    /**
     * Gestisce la richiesta GET: visualizza il form di pagamento per la spesa selezionata.
     */
    private function get(int $utenteId): void
    {
        $this->mostraForm($utenteId);
    }

    /**
     * Gestisce la richiesta POST: valida ed elabora il pagamento della spesa indicata.
     */
    private function post(int $utenteId): void
    {
        //Recupera i dati del form di pagamento e li valida 
        $idSpesa = filter_input(INPUT_POST, 'idSpesa', FILTER_VALIDATE_INT);
        $numeroCarta = trim((string) ($_POST['numeroCarta'] ?? ''));

        // Normalizza il numero della carta rimuovendo spazi e trattini
        $numeroCartaNormalizzato = preg_replace('/[\s-]+/', '', $numeroCarta);
        if ($numeroCartaNormalizzato === null) {
            $numeroCartaNormalizzato = '';
        }
        $nomeTitolare = trim((string) ($_POST['nomeTitolare'] ?? ''));
        $cognomeTitolare = trim((string) ($_POST['cognomeTitolare'] ?? ''));
        $dataScadenza = trim((string) ($_POST['dataScadenza'] ?? ''));
        $cvv = isset($_POST['cvv']) ? trim((string) $_POST['cvv']) : null;

        // Prepara oldData senza dati altamente sensibili (il CVV non viene mai ripopolato)
        $oldData = $_POST;
        unset($oldData['cvv']);

        // Controlla che tutti i campi obbligatori siano presenti e validi
        if ($idSpesa === false || $idSpesa === null || $dataScadenza === '') {
            $this->mostraErroreForm(
                'Parametri del pagamento non validi.',
                $idSpesa,
                $oldData
            );
            return;
        }

        // Converte la data di scadenza in oggetto DateTimeImmutable e gestisce eventuali errori
        try {
            $scadenza = new DateTimeImmutable($dataScadenza);
        } catch (\Exception) {
            $this->mostraErroreForm(
                'La data di scadenza non è valida.',
                $idSpesa,
                $oldData
            );
            return;
        }

        // Tutto pronto per tentare il pagamento della spesa
        try {
            $pagamento = $this->service->pagaSpesa(
                $utenteId,
                $idSpesa,
                $numeroCartaNormalizzato,
                $nomeTitolare,
                $cognomeTitolare,
                $scadenza,
                $cvv
            );
            $this->service->confermaPagamento($pagamento);
        } catch (\InvalidArgumentException $e) {
            $this->mostraErroreForm($e->getMessage(), $idSpesa, $oldData);
            return;
        } catch (\Throwable $e) {
            $this->mostraErroreForm('Si è verificato un errore durante l\'elaborazione del pagamento.', $idSpesa, $oldData);
            return;
        }

        $this->redirect('/home/mie_spese?successo=true');
    }

    // Mostra il form di pagamento per una spesa specifica
    private function mostraForm(int $utenteId): void
    {
        // Recupera l'ID della spesa dai parametri GET e lo valida
        $idSpesa = filter_input(INPUT_GET, 'idSpesa', FILTER_VALIDATE_INT);
        if ($idSpesa === false || $idSpesa === null) {
            $this->redirect('/home/mie_spese?errore=Spesa+non+valida');
        }

        try {
            $spesa = $this->service->getSpesaPerUtente($utenteId, $idSpesa);
        } catch (\InvalidArgumentException $e) {
            $this->redirect('/home/mie_spese?errore=' . rawurlencode($e->getMessage()));
        }

        // Recupera i dati dell'utente e mostra il form di pagamento
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
        header('Location: ' . $this->contextPath . $path);
        exit;
    }
}
