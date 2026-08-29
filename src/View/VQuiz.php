<?php

namespace CamassoMedelago\DriveMeSafely\View;

use CamassoMedelago\DriveMeSafely\Entity\EIscritto;
use CamassoMedelago\DriveMeSafely\Entity\EQuiz;
use CamassoMedelago\DriveMeSafely\Service\SQuiz;
use CamassoMedelago\DriveMeSafely\Smarty\StartSmarty;

class VQuiz
{
    public function showLista(array $quiz, EIscritto $utente): void
    {
        $smarty = StartSmarty::configuration();
        $smarty->assign('quizList', $quiz);
        $smarty->assign('utenteLoggato', $utente);
        $smarty->display('sezione_quiz.tpl');
    }

    public function showSvolgimento(EQuiz $quiz, array $domande): void
    {
        $smarty = StartSmarty::configuration();
        $smarty->assign('quiz', $quiz);
        $smarty->assign('listaDomande', $domande);
        $smarty->display('quiz_svolgimento.tpl');
    }

    public function showEsitoFromId(SQuiz $service, int $id, int $idIscritto): void
    {
        $smarty = StartSmarty::configuration();
        $smarty->assign(
            'riepilogo',
            $service->riepilogoQuiz($service->getSvolgimento($id, $idIscritto))
        );
        $smarty->display('quiz_esito.tpl');
    }

    public function showError(string $message, int $code): void
    {
        http_response_code($code);
        echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    }
}
