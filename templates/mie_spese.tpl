<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le mie Spese - DriveMeSafely</title>
    <link rel="stylesheet" href="{$request.contextPath}/css/style.css">
</head>
<body>
    <header>
        <h1>DriveMeSafely</h1>
        <nav>
            <ul>
                <li><a href="{if $utente instanceof \CamassoMedelago\DriveMeSafely\Entity\EProprietario}{$request.contextPath}/home/proprietario{else}{$request.contextPath}/home{/if}">Home</a></li>
                <li><a href="{$request.contextPath}/home/logout">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="spese-container">
        {if $report.patente}
            <h2>
                Pacchetto spese per {$report.patente->getTipo()|escape} -
                {$utente->getNome()|escape} {$utente->getCognome()|escape}
            </h2>
            <p>
                Dettaglio dei costi da sostenere per la patente
                {$report.patente->getTipo()|escape} -
                {$report.patente->getDescrizione()|escape}
            </p>
        {else}
            <h2>Situazione contabile delle spese della scuola guida</h2>
        {/if}

        {if $successo}
            <div class="payment-success" role="status">
                Pagamento effettuato con successo.
            </div>
        {/if}
        {if $errore}
            <div class="auth-alert" role="alert">{$errore|escape}</div>
        {/if}

        <div class="tabella-card">
            <table>
                <thead>
                    <tr>
                        <th>Spesa</th>
                        <th>Importo</th>
                        <th>Stato Pagamento</th>
                        <th>Data Pagamento</th>
                        <th>Azione</th>
                    </tr>
                </thead>
                <tbody>
                {if $report.righeSpese|@count > 0}
                    {foreach $report.righeSpese as $riga}
                        <tr>
                            <td>{$riga.spesa->getTipologia()|escape}</td>
                            <td>&euro;{$riga.spesa->getImporto()|number_format:2:',':'.'}</td>
                            <td>
                                {if $riga.pagamento}
                                    <span class="badge pagata">SALDATA</span>
                                {else}
                                    <span class="badge da-pagare">DA SALDARE</span>
                                {/if}
                            </td>
                            <td>
                                {if $riga.pagamento}
                                    {$riga.pagamento->getDataFormattata()}
                                {else}
                                    <span>-</span>
                                {/if}
                            </td>
                            <td>
                                {if $riga.pagamento}
                                    <span class="payment-done">Saldato</span>
                                {else}
                                    <a class="btn-paga"
                                       href="{$request.contextPath}/home/pagamento?idSpesa={$riga.spesa->getIdSpesa()}">
                                        Paga Ora
                                    </a>
                                {/if}
                            </td>
                        </tr>
                    {/foreach}
                {else}
                    <tr>
                        <td colspan="5">Nessuna spesa disponibile.</td>
                    </tr>
                {/if}
                </tbody>
            </table>
        </div>

        <div class="riepilogo">
            <div class="totale-box">
                Costo Totale Corso:
                <strong>&euro;{$report.costoTotale|number_format:2:',':'.'}</strong>
            </div>
            <div class="totale-box debito-box">
                Saldo:
                <strong>&euro;{$report.saldo|number_format:2:',':'.'}</strong>
            </div>
        </div>
    </main>

    <footer>
        <p>© {$smarty.now|date_format:"%Y"} DriveMeSafely - Le mie spese</p>
    </footer>
</body>
</html>
