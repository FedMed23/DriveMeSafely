<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Proprietario - DriveMeSafely</title>
    <link rel="stylesheet" href="{$request.contextPath}/css/style.css">
    <link rel="stylesheet" href="{$request.contextPath}/css/segreteria.css">
</head>
<body>
    <header>
        <h1>DriveMeSafely - Gestione Autoscuola</h1>
        <nav>
            <ul>
                <li><span class="staff-greeting">Ciao, {$utenteLoggato->getNome()}</span></li>
                <li><a href="{$homeUrl}">Home</a></li>
                <li><a href="{$request.contextPath}/home/logout">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="secretary-main">
        <h2>Pannello di Controllo Operativo</h2>
        <p>Seleziona un'area gestionale per accedere alle funzionalità dell'autoscuola.</p>
        <div class="dashboard-grid">
            <div class="card-amministrativa">
                <div>
                    <h3>💰 Cassa e pagamento spese</h3>
                    <p>Controlla i movimenti della cassa, le entrate e le uscite giornaliere.</p>
                </div>
                <a href="{$request.contextPath}/home/contabilita" class="btn-dashboard btn-purple">Apri Cassa</a>
            </div>
            <div class="card-amministrativa">
                <div>
                    <h3>📋 Gestisci spese</h3>
                    <p>Consulta tutte le spese dell'autoscuola e lo stato dei pagamenti.</p>
                </div>
                <a href="{$request.contextPath}/home/mie_spese" class="btn-dashboard btn-green">Apri Spese</a>
            </div>
        </div>
    </main>

    <footer>
        <p>© {$smarty.now|date_format:"%Y"} DriveMeSafely - Pannello Interno</p>
    </footer>
</body>
</html>
