<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prenotazioni lezioni - DriveMeSafely</title>
    <link rel="stylesheet" href="{$request.contextPath}/css/style.css">
    <link rel="stylesheet" href="{$request.contextPath}/css/calendario.css">
    <link rel="stylesheet" href="{$request.contextPath}/css/prenotazioni.css">
</head>
<body>
<header><h1>DriveMeSafely</h1><nav><ul><li><a href="{$request.contextPath}/home">Home</a></li><li><a href="{$request.contextPath}/home/prenotazioni">Le mie lezioni</a></li></ul></nav></header>
<main class="lezioni-container">
    <div class="action-banner">
        <div><p class="eyebrow">Area allievo</p><h2>Pannello attività e corsi</h2><p class="subtitle">Consulta gli appuntamenti o scegline uno nuovo.</p></div>
        <button type="button" class="tab-btn tab-btn-primary" data-tab-target="tab-prenota">➕ Nuova prenotazione</button>
    </div>
    {if $successo}<div class="alert alert-success" role="status">✔️ Operazione completata con successo.</div>{/if}
    {if $errore}<div class="alert alert-error" role="alert">⚠️ {$errore|escape}</div>{/if}

    <section id="tab-calendario" class="tab-content active">
        <div class="calendar-card">
            <div class="section-heading"><div><h3>Il tuo calendario</h3><p>Visualizza gli appuntamenti già confermati.</p></div><div class="calendar-legend"><span><i class="legend-dot pratica"></i> Guide pratiche</span><span><i class="legend-dot teoria"></i> Lezioni teoria</span></div></div>
            <div id="calendar"></div>
        </div>
        <section class="history-section">
            <div class="section-heading"><div><h3>📋 Storico prenotazioni</h3><p>Lo stato e i dettagli di tutte le attività prenotate.</p></div><button type="button" class="tab-btn tab-btn-outline" data-tab-target="tab-prenota">Prenota una lezione</button></div>
            <div class="table-card"><table><thead><tr><th>Tipologia</th><th>Data e ora</th><th>Dettagli</th><th>Stato</th><th>Azioni</th></tr></thead><tbody>
            {foreach $storicoPrenotazioni as $prenotazione}
                <tr>
                    <td>{if $prenotazione->getLezione() instanceof \CamassoMedelago\DriveMeSafely\Entity\ELezioneTeoria}<span class="badge tipo-teoria">📚 Teoria</span>{else}<span class="badge tipo-pratica">🚗 Pratica</span>{/if}</td>
                    <td><strong>{$prenotazione->getLezione()->getDataOra()->format('d/m/Y')}</strong><br><span class="muted">{$prenotazione->getLezione()->getDataOra()->format('H:i')}</span></td>
                    <td>{if $prenotazione->getLezione() instanceof \CamassoMedelago\DriveMeSafely\Entity\ELezioneTeoria}<strong>Aula:</strong> {$prenotazione->getLezione()->getAula()->getNomeEsteso()|escape}{else}<strong>Istruttore:</strong> {$prenotazione->getLezione()->getIstruttore()|default:'Non assegnato'|escape}<br><strong>Vettura:</strong> {$prenotazione->getLezione()->getVettura()|default:'Non assegnata'|escape}{/if}</td>
                    <td><span class="badge stato-{$prenotazione->getStato()->value|lower}">{$prenotazione->getStato()->getDescrizione()|escape}</span></td>
                    <td>{if $prenotazione->getStato()->isAttiva()}<form method="post" action="{$request.contextPath}/home/prenotazioni/annulla" class="inline-form"><input type="hidden" name="prenotazione" value="{$prenotazione->getIdPrenotazione()}"><button type="submit" class="btn-annulla">Annulla</button></form>{else}<span class="muted">—</span>{/if}</td>
                </tr>
            {foreachelse}<tr><td colspan="5" class="empty-state">Non hai ancora effettuato prenotazioni.</td></tr>{/foreach}
            </tbody></table></div>
        </section>
    </section>

    <section id="tab-prenota" class="tab-content" aria-labelledby="titolo-prenotazione">
        <div class="booking-header"><button type="button" class="tab-btn tab-btn-back" data-tab-target="tab-calendario">← Torna al calendario</button><div><p class="eyebrow">Nuova prenotazione</p><h3 id="titolo-prenotazione">Scegli la tua attività</h3><p>Le guide pratiche già assegnate a un altro allievo non vengono mostrate.</p></div></div>
        <div class="flex-forms">
            <article class="form-box pratica-box"><div class="form-icon">🚗</div><h3>Prenota una guida pratica</h3><p>Seleziona uno slot con istruttore e vettura assegnati dalla segreteria.</p><form action="{$request.contextPath}/home/prenotazioni" method="post"><input type="hidden" name="tipoLezione" value="PRATICA"><label for="lezionePratica">Slot di guida disponibili</label><select id="lezionePratica" name="lezione" required><option value="">-- Seleziona un appuntamento --</option>{foreach $lezioniDisponibili as $lezione}{if $lezione instanceof \CamassoMedelago\DriveMeSafely\Entity\ELezionePratica}<option value="{$lezione->getIdLezione()}">{$lezione->getDataOra()->format('d/m/Y H:i')} · {$lezione->getIstruttore()|escape}</option>{/if}{/foreach}</select><button type="submit" class="btn-submit btn-pratica">Conferma guida</button></form></article>
            <article class="form-box teoria-box"><div class="form-icon">📚</div><h3>Prenota una lezione di teoria</h3><p>Iscriviti a una lezione in aula fino al raggiungimento della capienza.</p><form action="{$request.contextPath}/home/prenotazioni" method="post"><input type="hidden" name="tipoLezione" value="TEORIA"><label for="lezioneTeoria">Lezioni disponibili</label><select id="lezioneTeoria" name="lezione" required><option value="">-- Seleziona una lezione --</option>{foreach $lezioniDisponibili as $lezione}{if $lezione instanceof \CamassoMedelago\DriveMeSafely\Entity\ELezioneTeoria}<option value="{$lezione->getIdLezione()}">{$lezione->getDataOra()->format('d/m/Y H:i')} · {$lezione->getAula()->getNomeEsteso()|escape}</option>{/if}{/foreach}</select><button type="submit" class="btn-submit btn-teoria">Conferma lezione</button></form></article>
        </div>
    </section>
</main>
<footer><p>© {$smarty.now|date_format:'%Y'} DriveMeSafely · Gestione corsi</p></footer>
<script>
window.eventiScuolaGuida = [
{foreach $storicoPrenotazioni as $prenotazione}
    {
        title: '{if $prenotazione->getStato()->value === "ANNULLATA"}[ANNULLATA] {/if}{if $prenotazione->getLezione() instanceof \CamassoMedelago\DriveMeSafely\Entity\ELezioneTeoria}Teoria{else}Guida pratica{/if}',
        start: '{$prenotazione->getLezione()->getDataOra()->format('c')}',
        color: '{if $prenotazione->getStato()->value === "ANNULLATA"}#a0aec0{elseif $prenotazione->getLezione() instanceof \CamassoMedelago\DriveMeSafely\Entity\ELezioneTeoria}#6b46c1{else}#2b6cb0{/if}',
        allDay: false
    }{if !$prenotazione@last},{/if}
{/foreach}
];
</script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js"></script>
<script src="{$request.contextPath}/js/lezione_calendar.js"></script>
<script src="{$request.contextPath}/js/prenotazioni.js"></script>
</body>
</html>
