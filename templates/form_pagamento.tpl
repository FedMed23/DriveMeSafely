<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dettagli Pagamento - DriveMeSafely</title>
    <link rel="stylesheet" href="{$request.contextPath}/css/style.css">
</head>
<body>
    <header>
        <h1>DriveMeSafely</h1>
        <nav>
            <ul>
                <li><a href="{$homeUrl}">Home</a></li>
                <li><a href="{$request.contextPath}/home/mie_spese">Torna alle Spese</a></li>
            </ul>
        </nav>
    </header>

    <main style="padding: 30px; max-width: 500px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2>Saldatura Spesa Corrente</h2>
        <p>
            Stai pagando: <strong>{$spesa->getTipologia()|escape}</strong>
            per un importo di
            <span style="color:#c0392b; font-weight:bold;">
                &euro;{$spesa->getImporto()|number_format:2:',':'.'}
            </span>
        </p>

        {if $errore}
            <div class="auth-alert" role="alert">{$errore|escape}</div>
        {/if}

        <form action="{$request.contextPath}/home/pagamento" method="POST" style="margin-top: 20px;">
            <input type="hidden" name="idSpesa" value="{$spesa->getIdSpesa()}">

            <div class="auth-field">
                <label for="numeroCarta">Numero Carta di Credito:</label>
                <input type="text" id="numeroCarta" name="numeroCarta"
                       placeholder="16 cifre"
                       value="{$oldData.numeroCarta|default:''|escape}"
                       inputmode="numeric" autocomplete="cc-number"
                      maxlength="19" pattern="{literal}[0-9][0-9 \-]{14,17}[0-9]{/literal}" required>
            </div>
            <br>
            <div class="auth-field">
                <label for="nomeTitolare">Nome Titolare:</label>
                <input type="text" id="nomeTitolare" name="nomeTitolare"
                       value="{$oldData.nomeTitolare|default:''|escape}"
                       autocomplete="cc-given-name" required>
            </div>
            <br>
            <div class="auth-field">
                <label for="cognomeTitolare">Cognome Titolare:</label>
                <input type="text" id="cognomeTitolare" name="cognomeTitolare"
                       value="{$oldData.cognomeTitolare|default:''|escape}"
                       autocomplete="cc-family-name" required>
            </div>
            <br>
            <div class="auth-field">
                <label for="dataScadenza">Data di Scadenza:</label>
                <input type="date" id="dataScadenza" name="dataScadenza"
                       value="{$oldData.dataScadenza|default:''|escape}"
                       autocomplete="cc-exp" required>
            </div>
            <br><br>
            <button type="submit" class="btn"
                    style="width: 100%; background-color: #27ae60;">
                Conferma Pagamento
            </button>
        </form>
    </main>

    <footer>
        <p>© {$smarty.now|date_format:"%Y"} DriveMeSafely - Transazioni Protette</p>
    </footer>
</body>
</html>
