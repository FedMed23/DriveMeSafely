<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Svolgimento Quiz - DriveMeSafely</title>
    <link rel="stylesheet" href="{$request.contextPath}/css/style.css">
</head>
<body>
    <main class="spese-container">
        <h2>Simulazione: {$quiz->getNome()|escape}</h2>
        <p>Rispondi a tutte le domande selezionando Vero o Falso.</p>
        <form action="{$request.contextPath}/home/quiz/svolgimento" method="post">
            <input type="hidden" name="idQuiz" value="{$quiz->getIdQuiz()}">
            {foreach $listaDomande as $indice => $domanda}
                <div class="card">
                    <p><strong>{$indice + 1}. {$domanda->getContenuto()|escape}</strong></p>
                    {if $domanda->getImmagine()}
                        <img src="{$request.contextPath}/images/quiz/{$domanda->getImmagine()|escape}" alt="Immagine domanda" style="max-width: 200px;">
                    {/if}
                    <label><input type="radio" name="risposta_{$domanda->getIdDomanda()}" value="true" required> VERO</label>
                    <label><input type="radio" name="risposta_{$domanda->getIdDomanda()}" value="false" required> FALSO</label>
                </div>
            {/foreach}
            <button type="submit" class="btn">Invia e correggi scheda</button>
        </form>
    </main>
</body>
</html>
