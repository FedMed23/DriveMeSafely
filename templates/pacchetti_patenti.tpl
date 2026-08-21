<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pacchetti Patenti - DriveMeSafely</title>
        <link rel="stylesheet" href="{$smarty.server.REQUEST_SCHEME}://{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}">
        <link rel="stylesheet" href="/DriveMeSafely/public/css/style.css">
        <link rel="stylesheet" href="/DriveMeSafely/public/css/pacchetti.css">
    </head>
    <body>
    <header>
        <h1>DriveMeSafely</h1>
        <nav>
            <ul>
                <li>
                    <a href="index.php?page=home">Home</a>
                </li>
                <li>
                    <a href="index.php?page=profilo" class="btn">
                        Profilo
                    </a>
                </li>
                <li>
                    <a href="index.php?page=logout">
                        Logout
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <main class="pacchetti-container">
        <div class="pacchetti-header">
            <h2>Pacchetti delle patenti</h2>
            <p>
                Scegli la patente alla quale desideri iscriverti.
            </p>
        </div>
        <div class="pacchetti-grid">
            {if !empty($pacchetti)}
                {foreach $pacchetti as $pacchetto}
                    <div class="pacchetto-card">
                        <span class="badge-patente">
                            Patente {$pacchetto->getPatente()->getTipo()}
                        </span>
                        <h3>
                            Patente {$pacchetto->getPatente()->getTipo()}
                        </h3>

                        <p class="pacchetto-descrizione">
                            {$pacchetto->getPatente()->getDescrizione()}
                        </p>

                        <div class="pacchetto-prezzo">
                            <span class="pacchetto-prezzo-label">
                                Costo complessivo
                            </span>
                            <span class="pacchetto-prezzo-valore">
                                {$pacchetto->getImportoTotale()|number_format:2:",":"."} €
                            </span>
                        </div>
                        <a href="index.php?page=pacchetto&idPa={$pacchetto->getPatente()->getId()}"
                        class="btn-pacchetto">
                            Visualizza pacchetto
                        </a>
                    </div>
                {/foreach}
            {else}
                <div class="pacchetti-vuoti">
                    Nessun pacchetto disponibile al momento.
                </div>
            {/if}
        </div>
    </main>
    <footer>
        <p>
            © {$smarty.now|date_format:"%Y"} DriveMeSafely
        </p>
    </footer>

    </body>
</html>