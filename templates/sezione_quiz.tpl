<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - DriveMeSafely</title>
    <link rel="stylesheet" href="{$request.contextPath}/css/style.css">
    <link rel="stylesheet" href="{$request.contextPath}/css/quiz.css">
</head>
<body>
    <header>
        <h1>DriveMeSafely</h1>
        <nav>
            <ul>
                <li><a href="{$request.contextPath}/home">Home</a></li>
                <li><a href="{$request.contextPath}/home/logout">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main class="spese-container quiz-list">
        <h2>Simulazioni Quiz Teoria</h2>
        <p>Scegli la scheda d'esame su cui desideri esercitarti.</p>
        <section class="quiz-dashboard" aria-label="Le tue statistiche quiz">
            <div class="quiz-stat-cards">
                <div class="quiz-stat-card"><span>Quiz svolti</span><strong>{$statisticheQuiz.totale}</strong></div>
                <div class="quiz-stat-card"><span>Quiz idonei</span><strong>{$statisticheQuiz.superati}</strong></div>
                <div class="quiz-stat-card"><span>Idoneità</span><strong>{$statisticheQuiz.percentualeIdoneita}%</strong></div>
            </div>
            <div class="quiz-charts">
                <article class="quiz-chart-card"><h3>Attività svolta</h3><p>Simulazioni completate nel tuo percorso.</p><canvas id="grafico-quiz-totali" aria-label="Grafico quiz svolti"></canvas></article>
                <article class="quiz-chart-card"><h3>Esiti delle simulazioni</h3><p>Confronto tra quiz idonei e da ripetere.</p><canvas id="grafico-idoneita" aria-label="Grafico idoneità quiz"></canvas></article>
            </div>
        </section>
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
    <script>
        window.statisticheQuiz = {
            totale: {$statisticheQuiz.totale},
            superati: {$statisticheQuiz.superati},
            nonSuperati: {$statisticheQuiz.nonSuperati}
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script src="{$request.contextPath}/js/quiz_dashboard.js"></script>
</body>
</html>
