<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Iscrizione Scuola Guida - DriveMeSafely</title>
        <link rel="stylesheet" href="/css/style.css">
    </head>
    <body>
        <header>
            <h1>DriveMeSafely</h1>
            <nav>
                <ul>
                    <li><a href="index.php?page=home">Torna alla Home</a></li>
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
            <form id="iscrizione-form" action="index.php?page=iscrizione" method="POST">
                <input type="hidden" name="idPa" value="{$pacchetto->getPatente()->getId()}">
                <h3>1. Credenziali di Accesso</h3>
                <div>
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <br>
                <div>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <br>
                <div>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <br><hr><br>
                <h3>2. Dati Anagrafici</h3>
                <div>
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                <br>
                <div>
                    <label for="cognome">Cognome:</label>
                    <input type="text" id="cognome" name="cognome" required>
                </div>
                <br>
                <div>
                    <label for="codiceFiscale">Codice Fiscale:</label>
                    <input type="text" id="codiceFiscale" name="codiceFiscale" required maxlength="16">
                </div>
                <br>
                <div>
                    <label for="dataNascita">Data di Nascita:</label>
                    <input type="date" id="dataNascita" name="dataNascita" required>
                </div>
                <br>
                <div>
                    <label for="luogoNascita">Luogo di Nascita:</label>
                    <input type="text" id="luogoNascita" name="luogoNascita" required>
                </div>
                <br>
                <div>
                    <label for="indirizzo">Indirizzo di Residenza:</label>
                    <input type="text" id="indirizzo" name="indirizzo" required>
                </div>
                <br>
                <div>
                    <label for="telefono">Numero di Telefono:</label>
                    <input type="text" id="telefono" name="telefono" required>
                </div>
                <br><hr><br>
                <h3>3. Patente scelta</h3>
                <div>Iscrizione per la patente di tipologia:
                    <strong>{$pacchetto->getPatente()->getTipo()}</strong>
                </div>
                <br><br>
                <button type="submit" class="btn" style="width: 100%;">Conferma Iscrizione e Accedi</button>
            </form>
        </main>
        <footer>
            <p>© {$smarty.now|date_format:"%Y"} DriveMeSafely</p>
        </footer>
    </body>
</html>