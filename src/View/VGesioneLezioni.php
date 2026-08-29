<?php

namespace CamassoMedelago\DriveMeSafely\View;

use CamassoMedelago\DriveMeSafely\Entity\EAula;
use CamassoMedelago\DriveMeSafely\Entity\EArgomentoMinisteriale;
use CamassoMedelago\DriveMeSafely\Smarty\StartSmarty;

class VGesioneLezioni
{
    public function show(array $lezioni, ?string $successo, ?string $errore): void
    {
        $smarty = StartSmarty::configuration();
        $eventi = array_map(static function ($lezione): array {
            $pratica = $lezione instanceof \CamassoMedelago\DriveMeSafely\Entity\ELezionePratica;
            return [
                'title' => $pratica ? 'Guida pratica' : 'Lezione teoria',
                'start' => $lezione->getDataOra()->format(DATE_ATOM),
                'color' => $pratica ? '#2b6cb0' : '#6b46c1',
            ];
        }, $lezioni);
        $smarty->assign('lezioni', $lezioni);
        $smarty->assign('eventiCalendario', json_encode(
            $eventi,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
        ));
        $smarty->assign('aule', EAula::cases());
        $smarty->assign('argomenti', EArgomentoMinisteriale::cases());
        $smarty->assign('successo', $successo !== null);
        $smarty->assign('errore', $errore);
        $smarty->display('gestione_lezioni.tpl');
    }
}
