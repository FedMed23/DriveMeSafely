<?php

namespace CamassoMedelago\DriveMeSafely\View;

use CamassoMedelago\DriveMeSafely\Smarty\StartSmarty;

class VGestioneEsami
{
    public function show(array $calendario, array $idonei, ?string $errore, bool $successo, ?int $selezionato): void
    {
        $smarty = StartSmarty::configuration();
        $smarty->assign('esamiDisponibili', $calendario['esamiDisponibili']);
        $smarty->assign('storicoPrenotazioni', $calendario['storicoPrenotazioni']);
        $smarty->assign('iscrittiIdonei', $idonei);
        $smarty->assign('errore', $errore);
        $smarty->assign('successo', $successo);
        $smarty->assign('idEsameSelezionato', $selezionato);
        $smarty->display('gestione_esami.tpl');
    }
}
