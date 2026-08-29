<?php

namespace CamassoMedelago\DriveMeSafely\View;

use CamassoMedelago\DriveMeSafely\Entity\EProprietario;
use CamassoMedelago\DriveMeSafely\Entity\ESpesa;
use CamassoMedelago\DriveMeSafely\Smarty\StartSmarty;

class VPagamentoSpese
{
    public function showForm(
        ESpesa $spesa,
        ?string $errore = null,
        array $oldData = [],
        ?object $utente = null
    ): void {
        $smarty = StartSmarty::configuration();
        $smarty->assign('spesa', $spesa);
        $smarty->assign('errore', $errore);
        $smarty->assign('oldData', $oldData);

        $contextPath = $smarty->getTemplateVars('request')['contextPath'];
        $homeUrl = $utente instanceof EProprietario
            ? $contextPath . '/home/proprietario'
            : $contextPath . '/home';

        $smarty->assign('homeUrl', $homeUrl);
        $smarty->display('form_pagamento.tpl');
    }

    public function showError(string $message, int $code): void
    {
        http_response_code($code);
        echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    }
}
