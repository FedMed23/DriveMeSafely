<!DOCTYPE html><html lang="it"><head><meta charset="UTF-8"><title>Gestione lezioni</title><link rel="stylesheet" href="{$request.contextPath}/css/style.css"><link rel="stylesheet" href="{$request.contextPath}/css/calendario.css"></head><body>
<main class="container">
<h1>Gestione lezioni</h1>
{if $successo}<p class="success">Lezione inserita.</p>{/if}
{if $errore}<p class="error">{$errore|escape}</p>{/if}
<form method="post">
<label>Data e ora <input type="datetime-local" name="dataOra" required></label>
<label>Tipo <select name="tipoLezione"><option value="TEORIA">Teoria</option><option value="PRATICA">Pratica</option></select></label>
<label>Aula (teoria) <select name="aula">{foreach $aule as $aula}<option value="{$aula->value}">{$aula->getNomeEsteso()}</option>{/foreach}</select></label>
<label>Argomento (teoria) <select name="argomento">{foreach $argomenti as $argomento}<option value="{$argomento->value}">{$argomento->getDescrizione()}</option>{/foreach}</select></label>
<label>Istruttore (pratica) <input name="istruttore"></label>
<label>Vettura (pratica) <input name="vettura"></label>
<button type="submit">Inserisci lezione</button>
</form>
<h2>Palinsesto</h2>
<section class="calendar-card">
<div class="calendar-header"><h2>📅 Calendario lezioni</h2><p>Visualizza tutti gli slot inseriti nel palinsesto.</p></div>
<div id="calendar"></div>
<div class="calendar-legend"><span>🔵 Guide pratiche</span><span>🟣 Lezioni teoria</span></div>
</section>
{foreach $lezioni as $lezione}<p>{$lezione->getDataOra()->format('d/m/Y H:i')} - {$lezione}</p>{foreachelse}<p>Nessuna lezione.</p>{/foreach}
</main>
<script>window.eventiScuolaGuida = {$eventiCalendario nofilter};</script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js"></script>
<script src="{$request.contextPath}/js/lezione_calendar.js"></script>
</body></html>
