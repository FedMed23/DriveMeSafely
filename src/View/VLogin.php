<?php

namespace CamassoMedelago\DriveMeSafely\View;

use CamassoMedelago\DriveMeSafely\Smarty\StartSmarty;

class VLogin
{
    public function showForm(
        int $num1,
        int $num2,
        ?string $errore = null,
        array $oldData = []
    ): void {
        $smarty = StartSmarty::configuration();
        $smarty->assign('num1', $num1);
        $smarty->assign('num2', $num2);
        $smarty->assign('errore', $errore);
        $smarty->assign('oldData', $oldData);
        $smarty->display('login.tpl');
    }

    public function showError(string $message, int $code): void
    {
        http_response_code($code);
        echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    }
}
