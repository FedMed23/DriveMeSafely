<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Entity\EAula;
use CamassoMedelago\DriveMeSafely\Entity\EArgomentoMinisteriale;
use CamassoMedelago\DriveMeSafely\Entity\EDipendente;
use CamassoMedelago\DriveMeSafely\Foundation\FUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\Service\SLezione;
use CamassoMedelago\DriveMeSafely\Service\SPrenotazioneLezione;
use CamassoMedelago\DriveMeSafely\View\VGesioneLezioni;
use Doctrine\ORM\EntityManagerInterface;

class CGestioneLezioni
{
    private SLezione $service;
    private SPrenotazioneLezione $prenotazioni;
    private FUtenteRegistrato $fUtente;
    private VGesioneLezioni $view;

    public function __construct(EntityManagerInterface $em)
    {
        $this->service = new SLezione($em);
        $this->prenotazioni = new SPrenotazioneLezione($em);
        $this->fUtente = new FUtenteRegistrato($em);
        $this->view = new VGesioneLezioni();
    }

    public function gestioneLezioni(): void
    {
        $this->sessione();
        $id = $this->idSessione();
        $utente = $id === null ? null : $this->fUtente->getById($id);
        if (!$utente instanceof EDipendente) {
            $this->redirect('/home/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = new \DateTimeImmutable((string) ($_POST['dataOra'] ?? ''));
                if (($_POST['tipoLezione'] ?? '') === 'PRATICA') {
                    $this->service->inserisciPratica(
                        $data,
                        (string) ($_POST['istruttore'] ?? ''),
                        (string) ($_POST['vettura'] ?? '')
                    );
                } else {
                    $this->service->inserisciTeoria(
                        $data,
                        EAula::from((string) ($_POST['aula'] ?? '')),
                        EArgomentoMinisteriale::from((string) ($_POST['argomento'] ?? ''))
                    );
                }
                $this->redirect('/home/segreteria/gestione_lezioni?successo=true');
            } catch (\Throwable $e) {
                $this->redirect('/home/segreteria/gestione_lezioni?errore=' . rawurlencode($e->getMessage()));
            }
        }

        $this->view->show($this->service->getPalinsesto(), $_GET['successo'] ?? null, $_GET['errore'] ?? null);
    }

    private function sessione(): void { if (session_status() === PHP_SESSION_NONE) session_start(); }
    private function idSessione(): ?int { $id=$_SESSION['utenteLoggatoId']??null; return is_numeric($id)?(int)$id:null; }
    private function redirect(string $path): never { header('Location: '.rtrim(dirname($_SERVER['SCRIPT_NAME']), '/').$path); exit; }
}
