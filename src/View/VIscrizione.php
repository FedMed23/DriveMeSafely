<?php

namespace CamassoMedelago\DriveMeSafely\View;

use Smarty\Smarty;

class VIscrizione
{
    private Smarty $smarty;

    public function __construct()
    {
        $this->smarty = Smarty::configuration();
    }

    /**
     * Visualizza tutti i pacchetti patente disponibili.
     *
     * @param array $pacchetti
     */
    public function showPacchetti(array $pacchetti): void
    {
        $this->smarty->assign('pacchetti', $pacchetti);

        $this->smarty->display(
            'iscrizione/pacchetti_patenti.tpl'
        );
    }

    /**
     * Visualizza il dettaglio del pacchetto selezionato.
     *
     * @param object $pacchetto
     */
    public function showDettaglioPacchetto($pacchetto): void
    {
        $this->smarty->assign('pacchetto', $pacchetto);

        $this->smarty->display(
            'iscrizione/dettaglio_pacchetto.tpl'
        );
    }

    /**
     * Visualizza il form di iscrizione.
     *
     * @param object $pacchetto
     */
    public function showFormIscrizione($pacchetto, ?string $errore = null): void
    {
        $this->smarty->assign('pacchetto', $pacchetto);

        if ($errore !== null) {
            $this->smarty->assign('errore', $errore);
        }

        $this->smarty->display(
            'iscrizione/form_iscrizione.tpl'
        );
    }

    /**
     * Visualizza la conferma dell'iscrizione.
     *
     * @param object $iscritto
     */
    public function showConfermaIscrizione($iscritto): void
    {
        $this->smarty->assign('iscritto', $iscritto);

        $this->smarty->display(
            'iscrizione/conferma_iscrizione.tpl'
        );
    }
}
