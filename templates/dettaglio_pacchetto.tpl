<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dettaglio pacchetto - DriveMeSafely</title>
        <link rel="stylesheet" href="/css/style.css">
        <link rel="stylesheet" href="/css/pacchetti.css">
    </head>
    <body>
        <header>
            <h1>DriveMeSafely</h1>
            <nav>
                <ul>
                    <li><a href="index.php?page=home">Home</a></li>
                    <li><a href="index.php?page=profilo" class="btn">Profilo</a></li>
                    <li><a href="index.php?page=logout">Logout</a></li>
                </ul>
            </nav>
        </header>
        <main class="dettaglio-container">
            <div class="pagina-header">
                <h2>Dettaglio del pacchetto</h2>
                <p>Consulta tutte le spese previste per la patente selezionata.</p>
            </div>
            <section class="pacchetto-dettaglio">
                <div class="pacchetto-intestazione">
                    <div class="pacchetto-info">
                        <span class="badge-patente">
                            Patente {$pacchetto->getPatente()->getTipo()}
                        </span>
                        <h3>Pacchetto patente {$pacchetto->getPatente()->getTipo()}</h3>
                        <p>{$pacchetto->getPatente()->getDescrizione()}</p>
                    </div>
                </div>
                <div class="sezione-spese">
                    <h4>Spese comprese nel pacchetto</h4>
                    {if !empty($pacchetto->getSpese())}
                        <div class="spese-lista">
                            {foreach $pacchetto->getSpese() as $spesa}
                                <div class="spesa-riga">
                                    <div class="spesa-info">
                                        <span class="spesa-tipologia">
                                            {$spesa->getTipologia()}
                                        </span>
                                        {if $spesa->getDescrizione() !== null}
                                            <span class="spesa-descrizione">
                                                {$spesa->getDescrizione()}
                                            </span>
                                        {/if}
                                    </div>
                                    <span class="spesa-importo">
                                        {$spesa->getImporto()|number_format:2:",":"."} €
                                    </span>
                                </div>
                            {/foreach}
                        </div>
                    {else}
                        <div class="nessuna-spesa">
                            Nessuna spesa associata a questo pacchetto.
                        </div>
                    {/if}
                </div>
                <div class="totale-box">
                    <span class="totale-label">Totale pacchetto</span>
                    <span class="totale-importo">
                        {$pacchetto->getImportoTotale()|number_format:2:",":"."} €
                    </span>
                </div>
                <div class="azioni">
                    <a href="index.php?page=pacchetti_patenti"
                       class="btn-indietro">
                        ← Torna ai pacchetti
                    </a>
                    <a href="index.php?page=iscrizione&idPa={$pacchetto->getPatente()->getId()}"
                       class="btn-iscrizione">
                        Procedi con l'iscrizione
                    </a>
                </div>
            </section>
        </main>
        <footer>
            <p>© {$smarty.now|date_format:"%Y"} DriveMeSafely</p>
        </footer>
    </body>
</html>