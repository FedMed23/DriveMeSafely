<?php

namespace CamassoMedelago\DriveMeSafely\Controller;

use CamassoMedelago\DriveMeSafely\Foundation\FPagamento;
use CamassoMedelago\DriveMeSafely\Foundation\FCartaDiCredito;
use CamassoMedelago\DriveMeSafely\Foundation\FDipendente;
use CamassoMedelago\DriveMeSafely\Foundation\FSpesa;
use CamassoMedelago\DriveMeSafely\Foundation\FProprietario;

use CamassoMedelago\DriveMeSafely\Entity\EPagamento;
use CamassoMedelago\DriveMeSafely\Entity\ECartaDiCredito;
use CamassoMedelago\DriveMeSafely\Entity\ESpesa;
use CamassoMedelago\DriveMeSafely\Entity\EProprietario;

class CPagamentoStipendi
{
    private FPagamento $fPagamento;
    private FCartaDiCredito $fCarta;
    private FDipendente $fDipendente;
    private FSpesa $fSpesa;
    private FProprietario $fProprietario;

    public function __construct(
        FPagamento $fPagamento,
        FCartaDiCredito $fCarta,
        FDipendente $fDipendente,
        FSpesa $fSpesa,
        FProprietario $fProprietario
    ) {
        $this->fPagamento = $fPagamento;
        $this->fCarta = $fCarta;
        $this->fDipendente = $fDipendente;
        $this->fSpesa = $fSpesa;
        $this->fProprietario = $fProprietario;
    }

    // 1 - Area contabilità
    public function gestioneContabilita(): array
    {
        return [
            'dipendenti' => $this->fDipendente->findAll(),
            'spese' => $this->fSpesa->findAll()
        ];
    }

    // 2 - Visualizza dipendenti
    public function getDipendenti(): array
    {
        return $this->fDipendente->findAll();
    }
  
    // 3 - Filtra dipendenti per tipologia
    public function getDipendentiPerTipologia(string $tipologia): array
    {
        return $this->fDipendente->findByTipologia($tipologia);
    }
  
    // 6 - Inserimento carta
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

    // 5 - Pagamento stipendi
    public function pagaStipendi(
        EProprietario $proprietario,
        array $dipendenti,
        float $importo,
        ECartaDiCredito $carta
    ): array {

        $pagamentiCreati = [];

        foreach ($dipendenti as $dipendente) {

            // creo la spesa
            $spesa = new ESpesa("stipendio", $importo);

            $this->fSpesa->save($spesa);

            // creo il pagamento
            $pagamento = new EPagamento(
                $proprietario,
                $spesa,
                new \DateTimeImmutable(),
                "completato",
                $carta
            );

            $this->fPagamento->save($pagamento);

            $pagamentiCreati[] = $pagamento;
        }

        return $pagamentiCreati;
    }
}
