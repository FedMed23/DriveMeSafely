<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Front Controller: unico punto di ingresso dell'applicazione.
 *
 * Riceve ogni richiesta HTTP in arrivo da public/index.php e la instrada
 * verso il controller/metodo corretto, sulla base di una tabella di route
 * esplicita (nessuna introspezione dinamica del filesystem o chiamata
 * per nome di classe non validata).
 */
class CFrontController
{
    /**
     * Tabella delle route: associa ogni percorso logico dell'applicazione
     * alla coppia [classe controller, metodo] che deve gestirlo.
     *
     * Alcuni alias (es. 'pacchetti_patenti') puntano allo stesso caso
     * d'uso per compatibilità con link storici o nomi più naturali.
     */
    private const ROUTES = [
        'home' => [CHome::class, 'home'],
        'iscrizione' => [CIscrizione::class, 'iscrizione'],
        'pacchetti_patenti' => [CIscrizione::class, 'iscrizione'],
        'login' => [CLogin::class, 'login'],
        'logout' => [CLogin::class, 'logout'],
        'segreteria' => [CSegreteria::class, 'home'],
        'proprietario' => [CProprietario::class, 'dashboard'],
        'dashboard_proprietario' => [CProprietario::class, 'dashboard'],
        'pagamento' => [CPagamentoSpese::class, 'pagamento'],
        'mie_spese' => [CMieSpese::class, 'mieSpese'],
        'contabilita' => [CContabilita::class, 'contabilita'],
        'quiz' => [CQuiz::class, 'quiz'],
        'quiz/svolgimento' => [CSvolgimentoQuiz::class, 'svolgimento'],
        'quiz/esito' => [CQuiz::class, 'esito'],
        'prenotazioni' => [CPrenotazioneLezione::class, 'prenotazioni'],
        'prenotazioni/annulla' => [CPrenotazioneLezione::class, 'annulla'],
        'profilo' => [CProfilo::class, 'profilo'],
        'miei_esami' => [CMieiEsami::class, 'miei'],
        'segreteria/gestione_lezioni' => [CGestioneLezioni::class, 'gestioneLezioni'],
        'segreteria/gestione_esami' => [CGestioneEsami::class, 'gestione'],
        'segreteria/gestione_esami/annulla' => [CGestioneEsami::class, 'annulla'],
        'segreteria/gestione_esami/registra_esito' => [CGestioneEsami::class, 'registraEsito'],
        'segreteria/gestione_esami/modifica_esito' => [CGestioneEsami::class, 'modificaEsito'],
        'segreteria/gestione_esami/annulla_esito' => [CGestioneEsami::class, 'annullaEsito'],
        'segreteria/idoneita_esami' => [CIdoneitaEsami::class, 'idoneita'],
    ];

    /** Route usata quando il percorso richiesto non specifica nulla (es. "/"). */
    private const ROUTE_DEFAULT = 'home';

    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * Punto di ingresso: riceve l'URI grezzo della richiesta, ricava la route
     * e la instrada verso il controller corretto.
     */
    public function run(string $requestUri): void
    {
        $route = $this->risolviRoute($requestUri);
        $this->dispatch($route);
    }

    /**
     * Calcola il percorso base "pubblico" dell'applicazione, cioè quello da
     * usare nei redirect (header('Location: ...')) generati dai controller.
     *
     * Riflette esattamente come il browser ha raggiunto l'app: se l'URL
     * richiesto conteneva "/public" lo mantiene, altrimenti lo omette (caso
     * degli URL "puliti" resi possibili dal riscrittore .htaccess). In questo
     * modo i redirect non "saltano" mai da un URL pulito a uno con "/public".
     */
    public static function calcolaContextPath(): string
    {
        $percorsoRichiesto = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
        $percorsoScript = trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        $percorsoApp = str_ends_with($percorsoScript, '/public')
            ? substr($percorsoScript, 0, -strlen('/public'))
            : ($percorsoScript === 'public' ? '' : $percorsoScript);

        // Verifichiamo quale prefisso corrisponde davvero all'URL richiesto dal
        // browser, dal più specifico ("/public" incluso) al più generico
        // (deploy in root, nessuna sottocartella). Il prefisso vuoto è sempre
        // un candidato valido come ultima risorsa: nessun prefisso da togliere.
        foreach ([$percorsoScript, $percorsoApp, ''] as $prefisso) {
            if ($prefisso === '') {
                return '';
            }
            if ($percorsoRichiesto === $prefisso || str_starts_with($percorsoRichiesto, $prefisso . '/')) {
                return '/' . $prefisso;
            }
        }

        return '';
    }

    /**
     * Trasforma l'URI grezzo della richiesta (es. "/DriveMeSafely/segreteria/gestione_esami?x=1")
     * nel nome logico della route (es. "segreteria/gestione_esami"), rimuovendo
     * l'eventuale sottocartella di installazione e i parametri di query string.
     */
    private function risolviRoute(string $requestUri): string
    {
        $percorso = trim((string) parse_url($requestUri, PHP_URL_PATH), '/');
        $percorso = $this->rimuoviBasePath($percorso);

        if ($percorso === '') {
            return self::ROUTE_DEFAULT;
        }

        // Le route "home/segreteria/gestione_esami" e "segreteria/gestione_esami"
        // sono equivalenti: il prefisso "home/" è puramente estetico nei link dell'app.
        if (str_starts_with($percorso, self::ROUTE_DEFAULT . '/')) {
            $percorso = substr($percorso, strlen(self::ROUTE_DEFAULT) + 1);
        }

        return $percorso;
    }

    /**
     * Rimuove dal percorso il prefisso "tecnico" dell'installazione, così da
     * ottenere sempre la stessa route logica sia che l'app venga raggiunta
     * con l'URL completo (es. "/DriveMeSafely/public/login") sia con l'URL
     * "pulito" reso possibile dal riscrittore .htaccess (es. "/DriveMeSafely/login").
     *
     * Il nome dell'app (es. "DriveMeSafely") resta parte dell'URL pubblico:
     * viene rimosso solo per calcolare la route, non per generarla.
     */
    private function rimuoviBasePath(string $percorso): string
    {
        $percorsoScript = trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        $percorsoApp = str_ends_with($percorsoScript, '/public')
            ? substr($percorsoScript, 0, -strlen('/public'))
            : ($percorsoScript === 'public' ? '' : $percorsoScript);

        foreach ([$percorsoScript, $percorsoApp] as $prefisso) {
            if ($prefisso === '') {
                continue;
            }
            if ($percorso === $prefisso) {
                return '';
            }
            if (str_starts_with($percorso, $prefisso . '/')) {
                return substr($percorso, strlen($prefisso) + 1);
            }
        }

        return $percorso;
    }

    /**
     * Cerca la route nella tabella e, se trovata, istanzia il controller
     * (iniettando l'EntityManager) e invoca il metodo corrispondente.
     * Se la route non esiste, risponde con un 404.
     */
    private function dispatch(string $route): void
    {
        if (!isset(self::ROUTES[$route])) {
            $this->pagineNonTrovata();
            return;
        }

        [$classe, $metodo] = self::ROUTES[$route];
        $controller = new $classe($this->em, self::calcolaContextPath());
        $controller->$metodo();
    }

    private function pagineNonTrovata(): void
    {
        http_response_code(404);
        echo 'Pagina non trovata.';
    }
}