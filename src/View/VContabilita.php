<?php

namespace CamassoMedelago\DriveMeSafely\View;

use CamassoMedelago\DriveMeSafely\DTO\CassaDTO;
use CamassoMedelago\DriveMeSafely\Smarty\StartSmarty;

class VContabilita
{
    public function showCassa(CassaDTO $cassa): void
    {
        $smarty = StartSmarty::configuration();
        $smarty->assign('cassa', $cassa);
        $smarty->display('gestione_contabilita.tpl');
    }

    public function showError(string $message, int $code): void
    {
        http_response_code($code);
        echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    }
}
