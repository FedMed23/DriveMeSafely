<?php

namespace CamassoMedelago\DriveMeSafely\View;

use CamassoMedelago\DriveMeSafely\Smarty\StartSmarty;

class VIdoneitaEsami
{
    public function show(array $profiliQuiz): void
    {
        $smarty = StartSmarty::configuration();
        $smarty->assign('profiliQuiz', $profiliQuiz);
        $smarty->display('idoneita_esami.tpl');
    }

    public function showError(string $messaggio, int $codiceHttp = 400): void
    {
        http_response_code($codiceHttp);
        echo htmlspecialchars($messaggio, ENT_QUOTES, 'UTF-8');
    }
}
