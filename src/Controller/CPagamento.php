<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Foundation\FPagamento;
use CamassoMedelago\DriveMeSafely\Foundation\FCartaDiCredito;
use CamassoMedelago\DriveMeSafely\Entity\EPagamento;
use CamassoMedelago\DriveMeSafely\Entity\ECartaDiCredito;
use DateTimeImmutable;


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
        return $this->fPagamento->getPagamentiById($idUtente);
    }

    // Seleziona pagamento
    public function selezionaPagamento(int $idPagamento): ?EPagamento
    {
        return $this->fPagamento->getPagamentoById($idPagamento);
    }

    // Inserimento carta
    public function inserisciCarta(array $datiCarta): ECartaDiCredito
    {
        $carta = new ECartaDiCredito(
            $datiCarta['nome'],
            $datiCarta['cognome'],
            $datiCarta['scadenza'],
            $datiCarta['numero']
        );

        $this->fCarta->save($carta);

        return $carta;
    }

    // Conferma pagamento
    public function confermaPagamento(int $idPagamento, ECartaDiCredito $carta): void
    {
        $pagamento = $this->fPagamento->getPagamentoById($idPagamento);

        $pagamento->setCartaDiCredito($carta);
        $pagamento->setStato("pagato");

        $this->fPagamento->update($pagamento);
    }
}
        

          