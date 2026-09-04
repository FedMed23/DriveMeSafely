<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Svolgimento Quiz - DriveMeSafely</title>
    <link rel="stylesheet" href="{$request.contextPath}/css/style.css">
    <link rel="stylesheet" href="{$request.contextPath}/css/quiz.css">
</head>
<body>
    <header><h1>DriveMeSafely - Esame teoria</h1></header>
    <main class="quiz-sheet">
        <div class="quiz-sheet-header">
            <div><p class="quiz-eyebrow">Simulazione attiva</p><h2>{$quiz->getNome()|escape}</h2><p>Rispondi a tutte le domande selezionando Vero o Falso.</p></div>
            <div class="quiz-timer" aria-live="polite">Tempo rimasto <strong id="timer-display">--:--</strong></div>
        </div>
        <form id="quiz-form" action="{$request.contextPath}/home/quiz/svolgimento" method="post">
            <input type="hidden" name="idQuiz" value="{$quiz->getIdQuiz()}">
            {foreach $listaDomande as $indice => $domanda}
                <article class="quiz-question">
                    <p class="question-text"><span>{$indice + 1}</span>{$domanda->getContenuto()|escape}</p>
                    {if $domanda->getImmagine()}
                        <img src="{$request.contextPath}/images/quiz/{$domanda->getImmagine()|escape}" alt="Immagine della domanda {$indice + 1}" class="question-image">
                    {/if}
                    <div class="answer-options">
                        <label class="answer answer-true"><input type="radio" name="risposta_{$domanda->getIdDomanda()}" value="true"><span>VERO</span></label>
                        <label class="answer answer-false"><input type="radio" name="risposta_{$domanda->getIdDomanda()}" value="false"><span>FALSO</span></label>
                    </div>
                </article>
            {/foreach}
            <button type="submit" class="btn quiz-submit">Invia e correggi scheda</button>
        </form>
    </main>
    <div id="dati-quiz" data-tempo="{$quiz->getTempoMassimo()}" hidden></div>
    <script src="{$request.contextPath}/js/quiz_timer.js"></script>
</body>
</html>
