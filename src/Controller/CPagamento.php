<?php

class CPagamento
{
    public static function pagamento()
    {
        if (!CUtente::isLogged()) {
            header('Location: /login');
            return;
        }

        $pm = new FPersistentManager();
        $utente = unserialize($_SESSION['utente']);

        // -------- GET --------
        if ($_SERVER['REQUEST_METHOD'] == "GET") {

            $pagamenti = $pm->load("utenteId", $utente->getId(), "FPagamento");

            print_r($pagamenti); // debug

        }

        // -------- POST --------
        elseif ($_SERVER['REQUEST_METHOD'] == "POST") {

            $numero = $_POST['numero'];
            $nome = $_POST['nome'];
            $cognome = $_POST['cognome'];
            $scadenza = $_POST['scadenza'];
            $idPagamento = $_POST['idPagamento'];

            // crea carta
            $carta = new ECartaDiCredito(
                $numero,
                $nome,
                $cognome,
                $scadenza
            );

            $fcarta = new FCartaDiCredito();
            $fcarta->save($carta);

            // recupera pagamento
            $pagamento = $pm->load("id", $idPagamento, "FPagamento");

            $pagamento->setCarta($carta);
            $pagamento->setStato("pagato");

            $pm->updateObj($pagamento);

            echo "Pagamento completato";
        }
    }
}
