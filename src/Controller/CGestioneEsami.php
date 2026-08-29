<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Entity\EDipendente;
use CamassoMedelago\DriveMeSafely\Foundation\FUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\Service\SPrenotazioneEsame;
use CamassoMedelago\DriveMeSafely\View\VGestioneEsami;
use Doctrine\ORM\EntityManagerInterface;

class CGestioneEsami
{
    public function __construct(private EntityManagerInterface $em) {}

    public function gestione(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $id = $_SESSION['utenteLoggatoId'] ?? null;
        $utente = is_numeric($id) ? (new FUtenteRegistrato($this->em))->getById((int) $id) : null;
        if (!$utente instanceof EDipendente) {
            header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/home/login');
            exit;
        }
        $service = new SPrenotazioneEsame($this->em);
        $idEsame = filter_input(INPUT_GET, 'idEsame', FILTER_VALIDATE_INT);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $ids = array_map('intval', (array) ($_POST['idIscritti'] ?? []));
                $service->conferma($service->prenota((int) $id, (int) ($_POST['idEsame'] ?? 0), $ids));
                header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/home/segreteria/gestione_esami?successo=1');
                exit;
            } catch (\Throwable $e) {
                header('Location: ' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/home/segreteria/gestione_esami?errore=' . rawurlencode($e->getMessage()));
                exit;
            }
        }
        $this->render($service, $idEsame, $_GET['errore'] ?? null, isset($_GET['successo']));
    }

    private function render(SPrenotazioneEsame $service, ?int $idEsame, ?string $errore, bool $successo): void
    {
        $calendario = $service->getCalendario();
        (new VGestioneEsami())->show($calendario, $idEsame ? $service->getIscrittiIdonei($idEsame) : [], $errore, $successo, $idEsame);
    }
}
