<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esito Quiz - DriveMeSafely</title>
    <link rel="stylesheet" href="{$request.contextPath}/css/style.css">
</head>
<body>
    <main class="spese-container">
        <h2>Riepilogo valutazione</h2>
        <p>Quiz: <strong>{$riepilogo.quiz->getNome()|escape}</strong></p>
        <p>Data: {$riepilogo.data|date_format:"%d/%m/%Y %H:%M"}</p>
        {if $riepilogo.superato}
            <div class="payment-success">IDONEO - Hai commesso {$riepilogo.errori} errori.</div>
        {else}
            <div class="auth-alert">NON IDONEO - Hai commesso {$riepilogo.errori} errori.</div>
        {/if}
        {foreach $riepilogo.risposte as $risposta}
            <div class="card">
                <p><strong>{$risposta.domanda->getContenuto()|escape}</strong></p>
                <p>La tua risposta:
                    <strong>{if $risposta.rispostaUtente}VERO{else}FALSO{/if}</strong>
                    - Risposta esatta:
                    <strong>{if $risposta.domanda->isRispostaCorretta()}VERO{else}FALSO{/if}</strong>
                </p>
                {if $risposta.corretta}<span class="badge pagata">Esatta</span>{else}<span class="badge da-pagare">Errata</span>{/if}
            </div>
        {/foreach}
        <a class="btn" href="{$request.contextPath}/home/quiz">Fai un altro quiz</a>
    </main>
</body>
</html>
