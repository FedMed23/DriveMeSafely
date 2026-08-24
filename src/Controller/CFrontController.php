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
        $method = $_SERVER['REQUEST_METHOD'];
    
        // Estrae solo il path ignorando i parametri GET dopo il "?"
        $resource = parse_url($path, PHP_URL_PATH);
        
        // Rimuove il prefisso della cartella locale di XAMPP se presente
        $basePath = '/DriveMeSafely/public/';
        if (str_starts_with($resource, $basePath)) {
            $resource = substr($resource, strlen($basePath));
        }
        
        // Pulisce gli slash ai lati
        $resource = trim($resource, '/');

        if ($resource === '') {
            $resource = 'home';
        }
        
        $parts = explode('/', $resource);
        $route = $parts[0]; 

        $controllerName = "C" . ucfirst($route);
        $controllerClass = "CamassoMedelago\\DriveMeSafely\\Controller\\" . $controllerName;

        // Directory fisica dei controller per il controllo di sicurezza
        $dir = __DIR__; 
        $eledir = scandir($dir);

        // Controlla se la classe del controller esiste fisicamente sul disco
        if (in_array($controllerName . ".php", $eledir)) {
            $mainMethod = $route; 
                
            // SOLUZIONE: Passiamo l'EntityManager al costruttore del controller!
            $controllerInstance = new $controllerClass($this->em);
                
            if (method_exists($controllerInstance, $mainMethod)) {
                $controllerInstance->$mainMethod();
                return;
            }
        } // <--- Sistemata la graffa mancante qui!

        // Risposta standard se nessuna rotta coincide
        http_response_code(404);
        echo 'Pagina non trovata.';
    }
}

        
