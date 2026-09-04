<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione lezioni - DriveMeSafely</title>
    <link rel="stylesheet" href="{$request.contextPath}/css/style.css">
    <link rel="stylesheet" href="{$request.contextPath}/css/calendario.css">
    <link rel="stylesheet" href="{$request.contextPath}/css/prenotazioni.css">
</head>
<body>
<header><h1>DriveMeSafely - Area Personale</h1><nav><a href="{$request.contextPath}/home/segreteria">Home</a> <a href="{$request.contextPath}/home/logout">Logout</a></nav></header>
<main class="lezioni-container">
    <div class="action-banner">
        <div><p class="eyebrow">Area segreteria</p><h2>Palinsesto Corsi</h2><p class="subtitle">Consulta il calendario delle lezioni o inseriscine una nuova.</p></div>
        <button type="button" class="tab-btn tab-btn-primary" data-tab-target="tab-nuova-lezione">➕ Nuova lezione</button>
    </div>
    {if $successo}<div class="alert alert-success" role="status">✔️ {$successo|escape}</div>{/if}
    {if $errore}<div class="alert alert-error" role="alert">⚠️ {$errore|escape}</div>{/if}

    <section id="tab-calendario" class="tab-content active">
        <div class="calendar-card">
            <div class="section-heading"><div><h3>📅 Calendario lezioni</h3><p>Visualizza tutti gli slot inseriti nel palinsesto.</p></div><div class="calendar-legend"><span><i class="legend-dot pratica"></i> Guide pratiche</span><span><i class="legend-dot teoria"></i> Lezioni teoria</span></div></div>
            <div id="calendar"></div>
        </div>
        <section class="history-section">
            <div class="section-heading"><div><h3>📋 Elenco lezioni</h3><p>Gestisci le lezioni già inserite nel palinsesto.</p></div><button type="button" class="tab-btn tab-btn-outline" data-tab-target="tab-nuova-lezione">Inserisci una lezione</button></div>
            <div class="table-card"><table><thead><tr><th>Data e ora</th><th>Tipo</th><th>Dettagli</th><th>Azioni</th></tr></thead><tbody>
            {foreach $lezioni as $lezione}
                <tr>
                    <td><strong>{$lezione->getDataOra()->format('d/m/Y')}</strong><br><span class="muted">{$lezione->getDataOra()->format('H:i')}</span></td>
                    <td>{if $lezione instanceof \CamassoMedelago\DriveMeSafely\Entity\ELezionePratica}<span class="badge tipo-pratica">🚗 Guida Pratica</span>{else}<span class="badge tipo-teoria">📚 Teoria</span>{/if}</td>
                    <td>{if $lezione instanceof \CamassoMedelago\DriveMeSafely\Entity\ELezionePratica}<strong>Istruttore:</strong> {$lezione->getIstruttore()|default:'Non assegnato'|escape}<br><strong>Vettura:</strong> {$lezione->getVettura()|default:'Non assegnata'|escape}{else}<strong>Aula:</strong> {$lezione->getAula()->getNomeEsteso()|escape}<br><strong>Argomento:</strong> {$lezione->getArgomentoLezione()->getDescrizione()|escape}{/if}</td>
                    <td>
                        {if $lezione->isAnnullata()}
                            <span class="badge stato-annullata">ANNULLATA</span>
                        {else}
                            <form method="post" action="{$request.contextPath}/home/segreteria/gestione_lezioni" class="inline-form" onsubmit="return confirm('Sei sicuro di voler annullare questa lezione e le relative prenotazioni?');">
                                <input type="hidden" name="azione" value="annulla">
                                <input type="hidden" name="idLezione" value="{$lezione->getIdLezione()}">
                                <button type="submit" class="btn-annulla">Annulla</button>
                            </form>
                        {/if}
                    </td>
                </tr>
            {foreachelse}<tr><td colspan="4" class="empty-state">Nessuna lezione nel palinsesto.</td></tr>{/foreach}
            </tbody></table></div>
        </section>
    </section>

    <section id="tab-nuova-lezione" class="tab-content" aria-labelledby="titolo-nuova-lezione">
        <div class="booking-header"><button type="button" class="tab-btn tab-btn-back" data-tab-target="tab-calendario">← Torna al calendario</button><div><p class="eyebrow">Nuova lezione</p><h3 id="titolo-nuova-lezione">Inserisci una lezione nel palinsesto</h3><p>Compila i dettagli richiesti in base al tipo di lezione.</p></div></div>
        <article class="form-box">
            <form method="post" action="{$request.contextPath}/home/segreteria/gestione_lezioni">
                <label>Data e ora <input type="datetime-local" name="dataOra" step="1800" required></label>
                <label>Tipo <select name="tipoLezione"><option value="TEORIA">Teoria</option><option value="PRATICA">Pratica</option></select></label>
                <label>Aula (teoria) <select name="aula">{foreach $aule as $aula}<option value="{$aula->value}">{$aula->getNomeEsteso()}</option>{/foreach}</select></label>
                <label>Argomento (teoria) <select name="argomento">{foreach $argomenti as $argomento}<option value="{$argomento->value}">{$argomento->getDescrizione()}</option>{/foreach}</select></label>
                <label>Istruttore (pratica) <input name="istruttore" list="listaIstruttori" placeholder="Scegli o digita un nuovo nome"></label>
                <datalist id="listaIstruttori">{foreach $istruttoriSuggeriti as $nome}<option value="{$nome|escape}">{/foreach}</datalist>
                <label>Vettura (pratica) <input name="vettura" list="listaVetture" placeholder="Scegli o digita una nuova vettura"></label>
                <datalist id="listaVetture">{foreach $vettureSuggerite as $vettura}<option value="{$vettura|escape}">{/foreach}</datalist>
                <button type="submit" class="btn-submit btn-pratica">Inserisci lezione</button>
            </form>
        </article>
    </section>
</main>
<footer><p>© {$smarty.now|date_format:"%Y"} DriveMeSafely - Pannello Interno</p></footer>
<script>window.eventiScuolaGuida = {$eventiCalendario nofilter};</script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js"></script>
<script src="{$request.contextPath}/js/lezione_calendar.js"></script>
<script src="{$request.contextPath}/js/prenotazioni.js"></script>
</body>
</html>
