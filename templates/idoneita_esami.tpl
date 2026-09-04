<!doctype html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Idoneità Esami</title>
    <link rel="stylesheet" href="{$request.contextPath}/css/style.css">
    <link rel="stylesheet" href="{$request.contextPath}/css/tabella.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="header-brand">
                <h1>DriveMeSafely - Area Personale</h1>
            </div>
            <nav class="header-nav">
                <a href="{$request.contextPath}/home/segreteria">Home</a>
                <a href="{$request.contextPath}/home/logout">Logout</a>
            </nav>
        </div>
    </header>
    <main class="lezioni-container">
        <section class="page-header">
            <h2>📊 Idoneità degli iscritti</h2>
            <p>Un allievo è idoneo quando ha svolto almeno il 70% dei quiz disponibili e ha superato almeno il 70% dei quiz svolti.</p>
        </section>
        <section class="tabella-card">
            <div class="tabella-header">
                <h3>Situazione degli iscritti</h3>
            </div>
            <div class="tabella-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Allievo</th>
                            <th>Quiz svolti</th>
                            <th>Quiz superati</th>
                            <th>Idoneità</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $profiliQuiz as $profilo}
                            <tr>
                                <td><strong>{$profilo->getIscritto()->getCognome()} {$profilo->getIscritto()->getNome()}</strong></td>
                                <td>{$profilo->getPercentualeQuizSvolti()|string_format:"%.2f"}%</td>
                                <td>{$profilo->getPercentualeQuizSuperati()|string_format:"%.2f"}%</td>
                                <td>
                                    {if $profilo->isIdoneo()}
                                        <span class="stato-badge stato-superato">✓ IDONEO</span>
                                    {else}
                                        <span class="stato-badge stato-non-superato">✕ NON IDONEO</span>
                                    {/if}
                                </td>
                            </tr>
                        {foreachelse}
                            <tr>
                                <td colspan="4">Nessun profilo disponibile.</td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
        