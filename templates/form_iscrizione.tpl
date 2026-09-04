<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Iscrizione Scuola Guida - DriveMeSafely</title>
        <link rel="stylesheet" href="{$request.contextPath}/css/style.css">
        <style>
            .campo-errore { display: block; color: #b42318; margin-top: 4px; }
            .campo-non-valido { border: 1px solid #b42318; }
        </style>
    </head>
    <body>
        <header>
            <h1>DriveMeSafely</h1>
            <nav>
                <ul>
                    <li><a href="{$request.contextPath}/home">Torna alla Home</a></li>
                </ul>
            </nav>
        </header>
        <main style="padding: 30px; max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2>Modulo di Iscrizione Nuovi Allievi</h2>
            <p>Compila tutti i campi per creare il tuo account e iscriverti al corso di guida scelto.</p>
            {if isset($errore)}
                <div style="color: red; font-weight: bold; margin-bottom: 15px; padding: 10px; background-color: #f8d7da; border-radius: 4px;">
                    ⚠️ {$errore}
                </div>
            {/if}
            <form id="iscrizione-form" action="{$request.contextPath}/home/iscrizione" method="POST" data-tipo-patente="{if isset($pacchetto) && $pacchetto->getPatente()}{$pacchetto->getPatente()->getTipo()}{/if}">
                <input type="hidden" name="idPa" value="{if isset($pacchetto) && $pacchetto->getPatente()}{$pacchetto->getPatente()->getId()}{else}{$oldData.idPa|default:''|escape}{/if}">
                <h3>1. Credenziali di Accesso</h3>
                <div>
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="{$oldData.username|default:''|escape}" minlength="1" maxlength="100" autocomplete="username" required>
                </div>
                <br>
                <div>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="{$oldData.email|default:''|escape}" maxlength="100" autocomplete="email" required>
                </div>
                <br>
                <div>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" minlength="8" maxlength="64" autocomplete="new-password" required>
                </div>
                <br><hr><br>
                <h3>2. Dati Anagrafici</h3>
                <div>
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" value="{$oldData.nome|default:''|escape}" minlength="2" maxlength="50" pattern="[A-Za-zÀ-ÿ' \-]{ldelim}2,50{rdelim}" autocomplete="given-name" required>
                </div>
                <br>
                <div>
                    <label for="cognome">Cognome:</label>
                    <input type="text" id="cognome" name="cognome" value="{$oldData.cognome|default:''|escape}" minlength="2" maxlength="50" pattern="[A-Za-zÀ-ÿ' \-]{ldelim}2,50{rdelim}" autocomplete="family-name" required>
                </div>
                <br>
                <div>
                    <label for="codiceFiscale">Codice Fiscale:</label>
                    <input type="text" id="codiceFiscale" name="codiceFiscale" value="{$oldData.codiceFiscale|default:''|escape}" minlength="16" maxlength="16" pattern="[A-Za-z0-9]{ldelim}16{rdelim}" autocomplete="off" required>
                </div>
                <br>
                <div>
                    <label for="dataNascita">Data di Nascita:</label>
                    <input type="date" id="dataNascita" name="dataNascita" value="{$oldData.dataNascita|default:''|escape}" max="{$smarty.now|date_format:'%Y-%m-%d'}" autocomplete="bday" required>
                </div>
                <br>
                <div>
                    <label for="luogoNascita">Luogo di Nascita:</label>
                    <input type="text" id="luogoNascita" name="luogoNascita" value="{$oldData.luogoNascita|default:''|escape}" minlength="2" maxlength="100" pattern="[A-Za-zÀ-ÿ .'\-]{ldelim}2,100{rdelim}" autocomplete="off" required>
                </div>
                <br>
                <div>
                    <label for="indirizzo">Indirizzo di Residenza:</label>
                    <input type="text" id="indirizzo" name="indirizzo" value="{$oldData.indirizzo|default:''|escape}" minlength="5" maxlength="100" pattern="[A-Za-zÀ-ÿ0-9 .,'/\-]{ldelim}5,100{rdelim}" autocomplete="street-address" required>
                </div>
                <br>
                <div>
                    <label for="telefono">Numero di Telefono:</label>
                    <input type="tel" id="telefono" name="telefono" value="{$oldData.telefono|default:''|escape}" minlength="9" maxlength="15" pattern="\+?[0-9 ]{ldelim}9,15{rdelim}" autocomplete="tel" required>
                </div>
                <br><hr><br>
                <h3>3. Patente scelta</h3>
                {if isset($pacchetto) && $pacchetto->getPatente()}
                    <div>Iscrizione per la patente di tipologia:
                        <strong>{$pacchetto->getPatente()->getTipo()}</strong>
                    </div>
                {else}
                    <div>Iscrizione per la patente selezionata</div>
                {/if}
                <br><br>
                <button type="submit" class="btn" style="width: 100%;">Conferma Iscrizione e Accedi</button>
            </form>
        </main>
        <script src="{$request.contextPath}/js/iscrizione.js"></script>
        <footer>
            <p>© {$smarty.now|date_format:"%Y"} DriveMeSafely</p>
        </footer>
    </body>
</html>