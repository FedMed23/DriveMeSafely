<?php
namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Service\SIscrizione;
use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FPatente;
use CamassoMedelago\DriveMeSafely\Foundation\FSpesa;
use CamassoMedelago\DriveMeSafely\Controller\CIscrizione; // <--- AGGIUNTO IMPORT MANCANTE
use Doctrine\ORM\EntityManagerInterface;

class CFrontController
{
    private EntityManagerInterface $em;

    // Passiamo l'EntityManager tramite il costruttore
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
        
        // Pulisce gli slash ai lati (es: "/home/iscrizione/" diventa "home/iscrizione")
        $resource = trim($resource, '/');

        if ($resource === '') {
            $resource = 'home';
        }
        
        $parts = explode('/', $resource);
    
        // Rotte principali sotto "home" (es. /home/iscrizione)
        if (count($parts) >= 2 && $parts[0] === 'home') {
            switch ($parts[1]) {
                case 'pacchetti_patenti':
                    $this->CIscrizione::getPatenti();
                    return;
                case 'dettaglio_pacchetto':
                    $this->CIscrizione::getPatente();
                    return;
                case 'iscrizione':
                    $this->gestisciIscrizione($method);
                    return;
            }
        }
        
        // Rotta per la pagina principale (es. /home o indirizzo radice)
        if (count($parts) === 1 && $parts[0] === 'home') {
            this->CHome($em);
            echo "Benvenuto nella Home Page!";
            return;
        }

        // Risposta standard se nessuna rotta coincide
        http_response_code(404);
        echo 'Pagina non trovata.';
    }


    private function gestisciPacchettiPatenti(string $method): void
    {
        if ($method !== 'GET') {
            http_response_code(405);
            echo 'Metodo HTTP non consentito.';
            return;
        }
        $this->getCIscrizione()->pacchetti();
    }

    private function gestisciIscrizione(string $method): void
    {
        $controller = $this->getCIscrizione();
        if ($method === 'GET') {
            $controller->formIscrizione();
            return;
        }
        if ($method === 'POST') {
            $controller->iscrivi();
            return;
        }
        http_response_code(405);
        echo 'Metodo HTTP non consentito.';
    }
}
