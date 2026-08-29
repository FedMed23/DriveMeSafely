<?php
namespace CamassoMedelago\DriveMeSafely\Controller;

use Doctrine\ORM\EntityManagerInterface;

class CFrontController
{
    private EntityManagerInterface $em;

    // 1. Il Front Controller riceve l'EntityManager dal file index.php principale
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function run(string $path): void
    {
        $resource = trim((string) parse_url($path, PHP_URL_PATH), '/');
        $basePath = trim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($basePath !== '' && $resource === $basePath) {
            $resource = '';
        } elseif (
            $basePath !== ''
            && str_starts_with($resource, $basePath . '/')
        ) {
            $resource = substr($resource, strlen($basePath) + 1);
        }

        $resource = trim($resource, '/');
        $parts = $resource === '' ? [] : explode('/', $resource);
        $route = $parts[0] ?? 'home';
        if ($route === 'home' && isset($parts[1]) && $parts[1] !== '') {
            $route = $parts[1];
            if (isset($parts[2]) && $parts[2] !== '') {
                $route .= '/' . $parts[2];
            }
        }

        // Both links represent the same inscription use case.
        if ($route === 'pacchetti_patenti') {
            $route = 'iscrizione';
        }

        $routes = [
            'home' => ['class' => CHome::class, 'method' => 'home'],
            'iscrizione' => ['class' => CIscrizione::class, 'method' => 'iscrizione'],
            'login' => ['class' => CLogin::class, 'method' => 'login'],
            'logout' => ['class' => CLogin::class, 'method' => 'logout'],
            'segreteria' => ['class' => CSegreteria::class, 'method' => 'home'],
            'proprietario' => ['class' => CProprietario::class, 'method' => 'dashboard'],
            'dashboard_proprietario' => ['class' => CProprietario::class, 'method' => 'dashboard'],
            'pagamento' => ['class' => CPagamentoSpese::class, 'method' => 'pagamento'],
            'mie_spese' => ['class' => CMieSpese::class, 'method' => 'mieSpese'],
            'contabilita' => ['class' => CContabilita::class, 'method' => 'contabilita'],
            'quiz' => ['class' => CQuiz::class, 'method' => 'quiz'],
            'quiz/svolgimento' => ['class' => CQuiz::class, 'method' => 'svolgimento'],
            'quiz/esito' => ['class' => CQuiz::class, 'method' => 'esito'],
            'prenotazioni' => ['class' => CPrenotazioneLezione::class, 'method' => 'prenotazioni'],
            'segreteria/gestione_lezioni' => ['class' => CGestioneLezioni::class, 'method' => 'gestioneLezioni'],
            'segreteria/gestione_esami' => ['class' => CGestioneEsami::class, 'method' => 'gestione'],
        ];

        if (isset($routes[$route])) {
            $controller = $routes[$route];
            $controllerInstance = new $controller['class']($this->em);
            $controllerInstance->{$controller['method']}();
            return;
        }

        http_response_code(404);
        echo 'Pagina non trovata.';
    }
}

        
