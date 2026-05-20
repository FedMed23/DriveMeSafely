<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Foundation\FPagamento;
use CamassoMedelago\DriveMeSafely\Foundation\FCartaDiCredito;
use CamassoMedelago\DriveMeSafely\Foundation\FSpesa;

use CamassoMedelago\DriveMeSafely\Entity\EPagamento;
use CamassoMedelago\DriveMeSafely\Entity\ECartaDiCredito;
use CamassoMedelago\DriveMeSafely\Entity\ESpesa;
use CamassoMedelago\DriveMeSafely\Entity\EProprietario;

class CPagamentoSpese
{
    private FPagamento $fPagamento;
    private FCartaDiCredito $fCarta;
    private FSpesa $fSpesa;

    public function __construct(
        FPagamento $fPagamento,
        FCartaDiCredito $fCarta,
        FSpesa $fSpesa
    ) {
        $this->fPagamento = $fPagamento;
        $this->fCarta = $fCarta;
        $this->fSpesa = $fSpesa;
    }

    // 1-2 Visualizza spese
    public function getSpese(): array
    {
        return $this->fSpesa->findAll();
    }

    // 3 Seleziona spesa
    public function selezionaSpesa(int $idSpesa): ?ESpesa
    {
        return $this->fSpesa->findById($idSpesa);
    }

    // 4 Inserisci carta
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

    // 5 Conferma pagamento
    public function confermaPagamentoSpesa(
        EProprietario $proprietario,
        ESpesa $spesa,
        ECartaDiCredito $carta
    ): EPagamento {

        $pagamento = new EPagamento(
            $proprietario,
            $spesa,
            new \DateTimeImmutable(),
            "completato",
            $carta
        );

        $this->fPagamento->save($pagamento);

        return $pagamento;
    }
}
