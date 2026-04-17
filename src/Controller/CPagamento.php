<?php

namespace DriveMeSafely\src\Controller;

use DriveMeSafely\src\Foundation\FPagamento;
use DriveMeSafely\src\Foundation\FCartaDiCredito;
use DriveMeSafely\src\Entity\EPagamento;
use DriveMeSafely\src\Entity\ECartaDiCredito;

class CPagamento
{
    private FPagamento $fPagamento;
    private FCartaDiCredito $fCarta;

    public function __construct(FPagamento $fPagamento, FCartaDiCredito $fCarta)
    {
        $this->fPagamento = $fPagamento;
        $this->fCarta = $fCarta;
    }

    // Visualizza stato pagamenti
    public function getPagamenti(int $idUtente): array
    {
        return $this->fPagamento->getPagamentiUtente($idUtente);
    }

    // Seleziona pagamento
    public function selezionaPagamento(int $idPagamento): ?EPagamento
    {
        return $this->fPagamento->getPagamentoById($idPagamento);
    }

    // Scelta metodo pagamento
    public function scegliMetodo(int $idPagamento, string $metodo): void
    {
        $pagamento = $this->fPagamento->getPagamentoById($idPagamento);
        $pagamento->setMetodo($metodo);

        $this->fPagamento->update($pagamento);
    }

    // Inserimento carta
    public function inserisciCarta(array $datiCarta): ECartaDiCredito
    {
        $carta = new ECartaDiCredito(
            $datiCarta['numero'],
            $datiCarta['nome'],
            $datiCarta['cognome'],
            $datiCarta['scadenza']
        );

        $this->fCarta->save($carta);

        return $carta;
    }

    // Conferma pagamento
    public function confermaPagamento(int $idPagamento, ECartaDiCredito $carta): void
    {
        $pagamento = $this->fPagamento->getPagamentoById($idPagamento);

        $pagamento->setCarta($carta);
        $pagamento->setStato("pagato");

        $this->fPagamento->update($pagamento);
    }
}
        

          
