<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Il mio Profilo - DriveMeSafely</title>
    <link rel="stylesheet" href="{$request.contextPath}/css/style.css">
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

    <main class="spese-container">
        <h2>Il mio Profilo</h2>
        <p>Consulta i tuoi dati anagrafici e di iscrizione alla scuola guida.</p>

        <div class="tabella-card">
            <table>
                <tbody>
                    <tr>
                        <th>Nome</th>
                        <td>{$iscritto->getNome()|escape}</td>
                    </tr>
                    <tr>
                        <th>Cognome</th>
                        <td>{$iscritto->getCognome()|escape}</td>
                    </tr>
                    <tr>
                        <th>Username</th>
                        <td>{$iscritto->getUsername()|escape}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{$iscritto->getEmail()|escape}</td>
                    </tr>
                    <tr>
                        <th>Codice Fiscale</th>
                        <td>{$iscritto->getCodiceFiscale()|escape}</td>
                    </tr>
                    <tr>
                        <th>Data di nascita</th>
                        <td>{$iscritto->getDataNascita()->format('d/m/Y')}</td>
                    </tr>
                    <tr>
                        <th>Luogo di nascita</th>
                        <td>{$iscritto->getLuogoNascita()|escape}</td>
                    </tr>
                    <tr>
                        <th>Indirizzo</th>
                        <td>{$iscritto->getIndirizzo()|escape}</td>
                    </tr>
                    <tr>
                        <th>Telefono</th>
                        <td>{$iscritto->getNumeroTelefono()|escape}</td>
                    </tr>
                    <tr>
                        <th>Patente</th>
                        <td>{$iscritto->getTipoPatente()->getTipo()|escape}</td>
                    </tr>
                    <tr>
                        <th>Stato account</th>
                        <td>
                            {if $iscritto->isStatoUtente()}
                                <span class="badge pagata">ATTIVO</span>
                            {else}
                                <span class="badge da-pagare">DISATTIVO</span>
                            {/if}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

    <footer>
        <p>© {$smarty.now|date_format:"%Y"} DriveMeSafely - Il mio profilo</p>
    </footer>
</body>
</html>
