<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Entity\EDipendente;
use CamassoMedelago\DriveMeSafely\Entity\EProprietario;
use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FUtenteRegistrato;
use CamassoMedelago\DriveMeSafely\Service\SQuiz;
use CamassoMedelago\DriveMeSafely\View\VQuiz;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Controller dedicato allo svolgimento di un quiz da parte dell'allievo.
 *
 * Separato da CQuiz perché l'elaborazione della generazione/correzione
 * delle domande è più corposa rispetto alle semplici visualizzazioni
 * (lista quiz ed esito) gestite da CQuiz.
 */
class CSvolgimentoQuiz
{
    private SQuiz $service;
    private FUtenteRegistrato $fUtente;
    private VQuiz $view;
    private string $contextPath;

    public function __construct(EntityManagerInterface $em, string $contextPath = '')
    {
        $this->service = new SQuiz($em);
        $this->fUtente = new FUtenteRegistrato($em);
        $this->view = new VQuiz();
        $this->contextPath = $contextPath;
    }

    /**
     * Gestisce il caso d'uso dello svolgimento di un quiz.
     *
     * GET  -> genera il quiz e visualizza il form di svolgimento
     * POST -> corregge le risposte inviate e reindirizza all'esito
     */
    public function svolgimento(): void
    {
        $this->avviaSessione();
        $utente = $this->utenteIscritto();

        $idQuiz = filter_input(
            $_SERVER['REQUEST_METHOD'] === 'POST' ? INPUT_POST : INPUT_GET,
            'idQuiz',
            FILTER_VALIDATE_INT
        );
        if ($idQuiz === false || $idQuiz === null) {
            $this->redirect('/home/quiz');
        }

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->get($idQuiz, $utente);
                break;

            case 'POST':
                $this->post($idQuiz, $utente);
                break;

            default:
                http_response_code(405);
                $this->view->showError('Metodo HTTP non supportato.', 405);
                break;
        }
    }

    /**
     * Gestisce la richiesta GET: genera le domande del quiz e le salva in sessione per la correzione.
     */
    private function get(int $idQuiz, EIscritto $utente): void
    {
        try {
            $quiz = $this->service->getQuizById($idQuiz);
            $domande = $this->service->generaQuiz($idQuiz, $utente);

            // Inizializza la mappa delle simulazioni attive per supporto multi-tab
            if (!isset($_SESSION['quizAttivi']) || !is_array($_SESSION['quizAttivi'])) {
                $_SESSION['quizAttivi'] = [];
            }

            $_SESSION['quizAttivi'][$idQuiz] = [
                'idQuiz' => $idQuiz,
                'idDomande' => array_map(
                    static fn ($domanda): int => $domanda->getIdDomanda(),
                    $domande
                ),
                'timestampInizio' => time(),
                'tempoMassimoMinuti' => $quiz->getTempoMassimo(),
            ];

            // Retrocompatibilità per sessione singola
            $_SESSION['quizInCorso'] = $_SESSION['quizAttivi'][$idQuiz];

            $this->view->showSvolgimento($quiz, $domande);
        } catch (\InvalidArgumentException $e) {
            $this->view->showError($e->getMessage(), 400);
        }
    }

    /**
     * Gestisce la richiesta POST: corregge le risposte inviate e reindirizza alla pagina dell'esito.
     */
    private function post(int $idQuiz, EIscritto $utente): void
    {
        try {
            $quiz = $this->service->getQuizById($idQuiz);

            $risposte = [];
            foreach ($_POST as $nome => $valore) {
                if (str_starts_with((string) $nome, 'risposta_')) {
                    $risposte[substr((string) $nome, 9)] = $valore;
                }
            }

            // Recupera lo stato della simulazione (supportando sia quizAttivi sia quizInCorso)
            $quizInCorso = $_SESSION['quizAttivi'][$idQuiz] ?? $_SESSION['quizInCorso'] ?? null;
            if (!is_array($quizInCorso) || ($quizInCorso['idQuiz'] ?? null) !== $idQuiz) {
                throw new \InvalidArgumentException('La sessione del quiz non è più valida. Avvia nuovamente la simulazione.');
            }

            $idDomande = $quizInCorso['idDomande'] ?? [];
            if (!is_array($idDomande) || $idDomande === []) {
                throw new \InvalidArgumentException('Non sono state trovate domande per questa simulazione.');
            }

            // Controllo del tempo massimo sul server (+ 60 secondi di tolleranza di latenza di rete)
            $timestampInizio = $quizInCorso['timestampInizio'] ?? null;
            $tempoMassimoMinuti = $quizInCorso['tempoMassimoMinuti'] ?? $quiz->getTempoMassimo();
            if ($timestampInizio !== null) {
                $tempoTrascorsoSecondi = time() - $timestampInizio;
                $limiteSecondiConsentiti = ($tempoMassimoMinuti * 60) + 60; // 60s tolleranza submit

                if ($tempoTrascorsoSecondi > $limiteSecondiConsentiti) {
                    // Tempo ampiamente superato: consideriamo le risposte non valide o fuori tempo
                    throw new \InvalidArgumentException('Tempo massimo a disposizione per la simulazione superato.');
                }
            }

            $svolgimento = $this->service->correggiQuiz(
                $idQuiz,
                $utente,
                $risposte,
                $idDomande
            );
            $this->service->confermaSvolgimento($svolgimento);

            $_SESSION['ultimoSvolgimentoQuiz'] = $svolgimento->getIdSvolgimento();
            unset($_SESSION['quizAttivi'][$idQuiz]);
            unset($_SESSION['quizInCorso']);

            $this->redirect('/home/quiz/esito');
        } catch (\InvalidArgumentException $e) {
            $this->view->showError($e->getMessage(), 400);
        }
    }

    private function utenteIscritto(): EIscritto
    {
        $id = $_SESSION['utenteLoggatoId'] ?? null;
        if (!is_int($id) && !(is_string($id) && ctype_digit($id))) {
            $this->redirect('/home/login');
        }

        $utente = $this->fUtente->getById((int) $id);
        if ($utente === null) {
            $this->redirect('/home/login');
        }

        if ($utente instanceof EDipendente) {
            $this->redirect('/home/segreteria');
        }

        if ($utente instanceof EProprietario) {
            $this->redirect('/home/proprietario');
        }

        if (!$utente instanceof EIscritto) {
            $this->redirect('/home/login');
        }

        return $utente;
    }

    private function avviaSessione(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function redirect(string $path): never
    {
        header('Location: ' . $this->contextPath . $path);
        exit;
    }
}
