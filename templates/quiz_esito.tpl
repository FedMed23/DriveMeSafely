<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esito Quiz - DriveMeSafely</title>
    <link rel="stylesheet" href="{$request.contextPath}/css/style.css">
    <link rel="stylesheet" href="{$request.contextPath}/css/quiz.css">
</head>
<body>
<header>
    <h1>DriveMeSafely - Risultati</h1>
    <nav><ul><li><a href="{$request.contextPath}/home">Home</a></li><li><a href="{$request.contextPath}/home/quiz">Nuovo quiz</a></li></ul></nav>
</header>

<main class="quiz-result">
    <p class="quiz-eyebrow">Correzione completata</p>
    <h2>Riepilogo della simulazione</h2>
    <p class="result-meta">{$riepilogo->getQuiz()->getNome()|escape} · {$riepilogo->getData()|date_format:"%d/%m/%Y alle %H:%M"}</p>

    {if $riepilogo->isSuperato()}
        <section class="result-banner result-pass">
            <div class="result-icon">🎉</div>
            <div><h3>Sei idoneo!</h3><p>Hai commesso <strong>{$riepilogo->getErrori()}</strong> errori. Il limite per superare la scheda è 3.</p></div>
        </section>
    {else}
        <section class="result-banner result-fail">
            <div class="result-icon">📘</div>
            <div><h3>Continua a esercitarti</h3><p>Hai commesso <strong>{$riepilogo->getErrori()}</strong> errori. Per superare la scheda devi rimanere entro 3.</p></div>
        </section>
    {/if}

    <section class="result-stats" aria-label="Statistiche del quiz">
        <div><span>Errori</span><strong>{$riepilogo->getErrori()}</strong></div>
        <div><span>Limite massimo</span><strong>3</strong></div>
        <div><span>Risposte registrate</span><strong>{$riepilogo->getTotaleDomande()}</strong></div>
    </section>

    <section class="result-details">
        <div class="result-details-heading"><div><h3>Dettaglio correzione</h3><p>Rivedi ogni quesito e concentra lo studio sulle risposte errate.</p></div><a class="btn" href="{$request.contextPath}/home/quiz">Fai un altro quiz</a></div>

        {foreach $riepilogo->getRisposte() as $indice => $risposta}
            <article class="result-answer {if $risposta.corretta}answer-correct{else}answer-wrong{/if}">
                <div class="result-answer-heading">
                    <span class="question-number">{$indice + 1}</span>
                    <p>{$risposta.domanda->getContenuto()|escape}</p>
                    {if $risposta.corretta}<span class="answer-status status-correct">✔ Esatta</span>{else}<span class="answer-status status-wrong">✕ Errata</span>{/if}
                </div>
                {if $risposta.domanda->getImmagine()}
                    <img src="{$request.contextPath}/images/quiz/{$risposta.domanda->getImmagine()|escape}" alt="Immagine del quesito {$indice + 1}" class="result-image">
                {/if}
                <div class="answer-comparison">
                    <p><span>La tua risposta</span><strong>{if $risposta.rispostaUtente}VERO{else}FALSO{/if}</strong></p>
                    <p><span>Risposta corretta</span><strong>{if $risposta.domanda->isRispostaCorretta()}VERO{else}FALSO{/if}</strong></p>
                </div>
            </article>
        {foreachelse}
            <div class="result-empty">Non ci sono risposte registrate per questa simulazione.</div>
        {/foreach}
    </section>
</main>
<footer><p>© {$smarty.now|date_format:'%Y'} DriveMeSafely</p></footer>
</body>
</html>
