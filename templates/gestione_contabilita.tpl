<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gestione Contabilità - Proprietario</title>
        <link rel="stylesheet" href="{$request.contextPath}/css/style.css">
        <link rel="stylesheet" href="{$request.contextPath}/css/cassa.css">
    </head>
    <body>
        <header>
            <h1>DriveMeSafely - Area Personale</h1>
            <nav>
                <ul>
                    <li><a href="{$request.contextPath}/home/proprietario">Home</a></li>
                    <li><a href="{$request.contextPath}/home/logout">Logout</a></li>
                </ul>
            </nav>
        </header>

        <main class="contabilita-container">
            <div class="azioni-cassa">
                <a href="{$request.contextPath}/home/mie_spese" class="btn-gestione-spese">
                    <span class="btn-icon">💳</span>
                    <span>
                        <strong>Gestisci spese</strong>
                        <small>Visualizza e paga le spese</small>
                    </span>
                    <span class="btn-arrow">→</span>
                </a>
            </div>

            <div id="dati-cassa" data-entrate="{$cassa->getEntrate()}" data-uscite="{$cassa->getUscite()}"></div>

            <div class="grafico-container">
                <div class="grafico-header">
                    <h3>Situazione giornaliera</h3>
                    <p>Entrate e uscite della giornata</p>
                </div>
                <div class="grafico-wrapper">
                    <canvas id="graficoCassa"></canvas>
                    <div id="grafico-vuoto">
                        <strong>Nessun movimento</strong>
                        <span>Oggi non sono state registrate<br>entrate o uscite.</span>
                    </div>
                </div>
            </div>

            <h2>Movimenti di cassa giornalieri</h2>
            <div class="tabella-card">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Utente</th>
                            <th>Tipologia</th>
                            <th>Importo</th>
                        </tr>
                    </thead>
                    <tbody>
                        {if $cassa->getPagamenti()|@count > 0}
                            {foreach $cassa->getPagamenti() as $pagamento}
                                <tr>
                                    <td>{$pagamento->getDataFormattata()}</td>
                                    <td>{$pagamento->getUtenteRegistrato()->getNome()|escape} {$pagamento->getUtenteRegistrato()->getCognome()|escape}</td>
                                    <td>{$pagamento->getSpesa()->getTipologia()|escape}</td>
                                    <td>&euro;{$pagamento->getSpesa()->getImporto()|number_format:2:',':'.'}</td>
                                </tr>
                            {/foreach}
                        {else}
                            <tr>
                                <td colspan="4" style="text-align: center; color: #777;">
                                    Nessun movimento di cassa disponibile.
                                </td>
                            </tr>
                        {/if}
                    </tbody>
                </table>
            </div>
        </main>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="{$request.contextPath}/js/cassa.js?v=2"></script>

        <footer>
            <p>© {$smarty.now|date_format:"%Y"} DriveMeSafely - Le mie spese</p>
        </footer>
    </body>
</html>
