<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{$titolo}</title>
        <link rel="stylesheet" href="/DriveMeSafely/public/css/style.css">
    </head>
    <body>
        <header>
            <div class="logo">
                <h1>{$titolo}</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="{$request.contextPath}/home">Home</a></li>
                    <li><a href="{$request.contextPath}/corsi">Corsi</a></li>
                    <li><a href="{$request.contextPath}/patenti">Patenti</a></li>
                    <li><a href="{$request.contextPath}/chi-siamo">Chi siamo</a></li>
                    {if isset($utenteLoggato)}
                        <li style="color: #eb7f0c; font-weight: bold;">Ciao, {$utenteLoggato->getNome()}!</li>
                        <li><a href="{$request.contextPath}/home/profilo" class="btn">Profilo</a></li>
                        <li><a href="{$request.contextPath}/home/quiz" class="btn btn-alt">Quiz</a></li>
                        <li><a href="{$request.contextPath}/home/prenotazioni" class="btn">Prenota Lezioni</a></li>
                        <li><a href="{$request.contextPath}/home/miei_esami" class="btn">Esami</a></li>
                        <li><a href="{$request.contextPath}/home/mie_spese" class="btn">Spese</a></li>
                        <li><a href="{$request.contextPath}/home/logout">Logout</a></li>
                    {else}
                        <li><a href="{$request.contextPath}/home/iscrizione" class="btn">Iscriviti</a></li>
                        <li><a href="{$request.contextPath}/home/login">Login</a></li>
                    {/if}
                </ul>
            </nav>
        </header>
        <main>
            <section class="dove-siamo" style="margin-top: 50px; padding: 20px; text-align: center;">
                <h2>La nostra sede</h2>
                <p>Vieni a trovarci per le lezioni di teoria e l'iscrizione!</p>
                <div class="map-container" style="margin-top: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); display: inline-block; border-radius: 8px; overflow: hidden; width: 100%; max-width: 800px;">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d17603.791031647994!2d13.397766269280051!3d42.359763006853576!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x132fd3051151ed9f%3A0x6bb3f5f6e1749673!2sAutoscuola%20Gentile!5e0!3m2!1sit!2sit!4v1784909319393!5m2!1sit!2sit"
                        width="600"
                        height="450"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin">
                    </iframe>
                </div>
            </section>
            <section class="hero">
                {if isset($utenteLoggato)}
                    <h2>Bentornato sulla tua plancia di comando, {$utenteLoggato->getNome()}!</h2>
                    <p>I tuoi progressi sono al sicuro. Da qui puoi accedere alle tue lezioni e ai quiz.</p>
                    <div class="azioni-utente">
                        <a href="{$request.contextPath}/home/quiz" class="btn btn-alt">Fai un Quiz</a>
                    </div>
                {else}
                    <h2>La tua patente inizia da qui.</h2>
                    <p>Prenota le lezioni, segui il tuo percorso e monitora i tuoi progressi direttamente online.</p>
                    <a href="{$request.contextPath}/home/pacchetti_patenti" class="btn">Iscriviti subito</a>
                {/if}
            </section>
            <section class="servizi">
                <h2>I nostri servizi</h2>
                <div class="cards">
                    <div class="card">
                        <h3>Patente A</h3>
                        <p>Corso completo per moto e scooter.</p>
                    </div>
                    <div class="card">
                        <h3>Patente B</h3>
                        <p>Corso teorico e pratico per automobile.</p>
                    </div>
                    <div class="card">
                        <h3>Quiz Online</h3>
                        <p>Esercitati quando vuoi con migliaia di quiz.</p>
                    </div>
                </div>
            </section>
        </main>
        <footer>
            <p>© {$smarty.now|date_format:"%Y"} {$titolo}</p>
        </footer>
        <script src="{$request.contextPath}/js/home.js"></script>
    </body>
</html>
