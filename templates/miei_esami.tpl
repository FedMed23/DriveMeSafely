<!doctype html><html lang="it"><head><meta charset="UTF-8"><title>I miei Esami</title><link rel="stylesheet" href="{$request.contextPath}/css/style.css"><link rel="stylesheet" href="{$request.contextPath}/css/tabella.css"><link rel="stylesheet" href="{$request.contextPath}/css/prenotazioni.css"><link rel="stylesheet" href="{$request.contextPath}/css/calendario.css"></head><body>
<header><h1>DriveMeSafely - Area Personale</h1><nav><a href="{$request.contextPath}/home">Home</a> <a href="{$request.contextPath}/home/logout">Logout</a></nav></header>
<main class="lezioni-container"><h2>🎓 I miei Esami</h2>
<p>Consulta il calendario e lo storico delle tue prenotazioni d'esame. La prenotazione di un esame è gestita esclusivamente dalla segreteria.</p>
<section class="calendar-card">
<div class="calendar-header"><h2>📅 Calendario esami</h2><p>Visualizza le sessioni d'esame a cui sei iscritto.</p></div>
<div id="calendar"></div>
<div class="calendar-legend"><span>🔵 Esami pratici</span><span>🟣 Esami teoria</span></div>
</section>
<h3>📋 Storico prenotazioni</h3><table><thead><tr><th>Tipologia</th><th>Data</th><th>Stato</th><th>Esito</th></tr></thead><tbody>{foreach $storicoEsami as $p}{assign var="idPrenotazione" value=$p->getIdPrenotazioneEsame()}{if isset($effettuazioniPerPrenotazione[$idPrenotazione])}{assign var="effettuazione" value=$effettuazioniPerPrenotazione[$idPrenotazione]}{else}{assign var="effettuazione" value=null}{/if}<tr><td>{$p->getEsame()->getTipologia()->getDescrizione()}</td><td>{$p->getEsame()->getDataOraFormattata()}</td><td><span class="badge stato-{$p->getStato()->value|lower}">{$p->getStato()->getDescrizione()|escape}</span></td><td>{if $effettuazione}<span class="stato-badge {if $effettuazione->isSuperato()}stato-superato{else}stato-non-superato{/if}">{if $effettuazione->isSuperato()}✓ Superato{else}✕ Non superato{/if}</span>{else}<span class="muted">—</span>{/if}</td></tr>{foreachelse}<tr><td colspan="4">Nessuna prenotazione d'esame.</td></tr>{/foreach}</tbody></table>
</main>
<script>window.eventiEsami = {$eventiCalendario nofilter};</script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js"></script>
<script src="{$request.contextPath}/js/esame_calendar.js"></script>
</body></html>
