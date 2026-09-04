<?php

namespace CamassoMedelago\DriveMeSafely\View;

use CamassoMedelago\DriveMeSafely\Entity\TipologiaEsame;
use CamassoMedelago\DriveMeSafely\Smarty\StartSmarty;

class VGestioneEsami
{
    public function show(
        array $calendario,
        array $idonei,
        ?string $errore,
        mixed $successo,
        ?int $selezionato,
        array $effettuazioniPerPrenotazione = [],
        array $prenotazioniSvolte = []
    ): void
    {
        $smarty = StartSmarty::configuration();
        $eventi = array_map(static function ($esame): array {
            $pratica = $esame->getTipologia() === TipologiaEsame::PRATICA;
            return [
                'title' => $pratica ? 'Esame pratico' : 'Esame teoria',
                'start' => $esame->getDataEs()->format(DATE_ATOM),
                'color' => $pratica ? '#2b6cb0' : '#6b46c1',
            ];
        }, $calendario['esamiDisponibili']);
        $smarty->assign('esamiDisponibili', $calendario['esamiDisponibili']);
        $smarty->assign('storicoPrenotazioni', $calendario['storicoPrenotazioni']);
        $smarty->assign('eventiCalendario', json_encode(
            $eventi,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
        ));
        $smarty->assign('iscrittiIdonei', $idonei);
        $smarty->assign('errore', $errore);
        $smarty->assign('successo', $successo);
        $smarty->assign('idEsameSelezionato', $selezionato);
        $smarty->assign('effettuazioniPerPrenotazione', $effettuazioniPerPrenotazione);
        $smarty->assign('prenotazioniSvolte', $prenotazioniSvolte);
        $smarty->display('gestione_esami.tpl');
    }

    public function showError(string $messaggio, int $codiceHttp = 400): void
    {
        http_response_code($codiceHttp);
        echo htmlspecialchars($messaggio, ENT_QUOTES, 'UTF-8');
    }
}
