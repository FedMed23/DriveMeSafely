<?php

namespace CamassoMedelago\DriveMeSafely\View;
use CamassoMedelago\DriveMeSafely\Smarty\StartSmarty;

class VIscrizione
{
    public function __construct()
    {
        $smarty = StartSmarty::configuration();
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
    
        /**
     * Gestisce gli errori di compilazione dei moduli (InvalidArgument / Runtime)
     * Mostra un popup e torna indietro mantenendo i dati compilati dal browser.
     */
    public function showFormError(string $errorMessage, array $oldData = [], $pacchetto = null): void
    {
        echo "<script>alert('" . addslashes($errorMessage) . "'); window.history.back();</script>";
        exit;
    }

    /**
     * Gestisce gli errori imprevisti o di sistema (Throwable / default HTTP 405)
     * Risolve il Fatal Error aggiungendo il metodo mancante.
     */
    public function showError(string $errorMessage, int $code = 500): void
    {
        // Imposta il codice di stato HTTP per il browser
        http_response_code($code);
        
        // Mostra il popup di errore imprevisto e torna indietro
        echo "<script>alert('" . addslashes($errorMessage) . "'); window.history.back();</script>";
        exit;
    }

}
