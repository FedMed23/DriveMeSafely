<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - DriveMeSafely</title>
    <link rel="stylesheet" href="{$request.contextPath}/css/style.css">
</head>
<body>
    <header>
        <h1>DriveMeSafely</h1>
        <nav>
            <ul>
                <li><a href="{$homeUrl}">Home</a></li>
                <li><a href="{$request.contextPath}/home/logout">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main class="spese-container">
        <h2>Simulazioni Quiz Teoria</h2>
        <p>Scegli la scheda d'esame su cui desideri esercitarti.</p>
        <div class="cards">
        {if $quizList|@count > 0}
            {foreach $quizList as $quiz}
                <div class="card">
                    <h3>{$quiz->getNome()|escape}</h3>
                    <p><strong>Tempo:</strong> {$quiz->getTempoMassimo()} minuti</p>
                    <p><strong>Numero domande:</strong> {$quiz->getNumeroDomande()}</p>
                    <a class="btn" href="{$request.contextPath}/home/quiz/svolgimento?idQuiz={$quiz->getIdQuiz()}">Inizia Quiz</a>
                </div>
            {/foreach}
        {else}
            <p>Nessuna simulazione disponibile.</p>
        {/if}
        </div>
    </main>
    <footer><p>© {$smarty.now|date_format:"%Y"} DriveMeSafely</p></footer>
</body>
</html>
