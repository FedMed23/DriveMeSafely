<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Segreteria - DriveMeSafely</title>
    <link rel="stylesheet" href="{$request.contextPath}/css/style.css">
    <link rel="stylesheet" href="{$request.contextPath}/css/segreteria.css">
</head>
<body>
<header>
    <h1>DriveMeSafely - Gestione Autoscuola</h1>
    <nav><ul>
        <li><span class="staff-greeting">Ciao, {$utenteLoggato->getNome()} ({$utenteLoggato->getRuolo()})</span></li>
        <li><a href="{$request.contextPath}/home/segreteria">Home</a></li>
        <li><a href="{$request.contextPath}/home/logout">Logout</a></li>
    </ul></nav>
</header>
<main class="secretary-main">
    <h2>Pannello di Controllo Operativo</h2>
    <p>Seleziona un'area gestionale per accedere alle funzionalità della segreteria.</p>
    <div class="dashboard-grid">
        <div class="card-amministrativa"><div><h3>📅 Palinsesto Corsi</h3><p>Pianifica lezioni di teoria e guide pratiche.</p></div><a href="{$request.contextPath}/home/segreteria/gestione_lezioni" class="btn-dashboard">Apri Calendario</a></div>
        <div class="card-amministrativa"><div><h3>📝 Registro Lezioni</h3><p>Gestisci presenze, commenti e voti degli allievi.</p></div><a href="{$request.contextPath}/home/segreteria/registro" class="btn-dashboard btn-dark">Apri Registro</a></div>
        <div class="card-amministrativa"><div><h3>👥 Gestione Esami</h3><p>Visualizza e gestisci gli esami degli allievi.</p></div><a href="{$request.contextPath}/home/segreteria/gestione_esami" class="btn-dashboard btn-green">Apri Anagrafica</a></div>
        <div class="card-amministrativa"><div><h3>📊 Idoneità Esami</h3><p>Consulta l'idoneità agli esami in base ai quiz svolti.</p></div><a href="{$request.contextPath}/home/segreteria/idoneita_esami" class="btn-dashboard btn-orange">Apri Idoneità</a></div>
    </div>
</main>
<footer><p>© {$smarty.now|date_format:"%Y"} DriveMeSafely - Pannello Interno</p></footer>
</body>
</html>
