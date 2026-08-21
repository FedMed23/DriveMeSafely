<?php
namespace CamassoMedelago\DriveMeSafely\Controller;
use CamassoMedelago\DriveMeSafely\Service\SIscrizione;
use CamassoMedelago\DriveMeSafely\Foundation\FIscritto;
use CamassoMedelago\DriveMeSafely\Foundation\FPatente;
use CamassoMedelago\DriveMeSafely\Foundation\FSpesa;
class CFrontController
{
    public function run(string $path): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $resource = parse_url($path, PHP_URL_PATH);
        $resource = trim($resource, '/');
        $parts = explode('/', $resource);
        if (count($parts) >= 2 && $parts[0] === 'home') {
            switch ($parts[1]) {
                case 'pacchetti_patenti':
                    $this->gestisciPacchettiPatenti($method);
                    return;
                case 'iscrizione':
                    $this->gestisciIscrizione($method);
                    return;
            }
        }
        http_response_code(404);
        echo 'Pagina non trovata.';
    }
    private function getCIscrizione(): CIscrizione
    {
        $fIscritto = new FIscritto();
        $fPatente = new FPatente();
        $fSpesa = new FSpesa();
        $service = new SIscrizione(
            $fIscritto,
            $fPatente,
            $fSpesa
        );
        return new CIscrizione($service);
    }
    private function gestisciPacchettiPatenti(string $method): void
    {
        if ($method !== 'GET') {
            http_response_code(405);
            echo 'Metodo HTTP non consentito.';
            return;
        }
        $controller = $this->getCIscrizione();
        $controller->pacchetti();
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