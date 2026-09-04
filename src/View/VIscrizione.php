<?php

namespace CamassoMedelago\DriveMeSafely\View;
use CamassoMedelago\DriveMeSafely\Smarty\StartSmarty;

class VIscrizione
{
    private \Smarty\Smarty $smarty;

    public function __construct()
    {
        $this->smarty = StartSmarty::configuration();
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
            'pacchetti_patenti.tpl'
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
            'dettaglio_pacchetto.tpl'
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
        $this->smarty->assign('oldData', []);

        if ($errore !== null) {
            $this->smarty->assign('errore', $errore);
        }

        $this->smarty->display(
            'form_iscrizione.tpl'
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
            'iscrizione/VConfermaIscrizione.tpl'
        );
    }
    
        public function showFormError(string $errorMessage, array $oldData = [], $pacchetto = null): void
        {
            $this->smarty->assign('pacchetto', $pacchetto);
            $this->smarty->assign('errore', $errorMessage);
            $this->smarty->assign('oldData', $oldData);
            $this->smarty->display('form_iscrizione.tpl');
        }

    /**
     * Gestisce gli errori imprevisti o di sistema (Throwable / default HTTP 405)
     */
    public function showError(string $errorMessage, int $code = 500): void
    {
        http_response_code($code);
        echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8');
        exit;
    }

}
