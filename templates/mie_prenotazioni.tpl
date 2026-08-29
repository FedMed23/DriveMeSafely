<!DOCTYPE html><html lang="it"><head><meta charset="UTF-8"><title>Le mie lezioni</title><link rel="stylesheet" href="{$request.contextPath}/css/style.css"></head><body>
<main class="container">
<h1>Le mie lezioni</h1>
{if $successo}<p class="success">Prenotazione confermata.</p>{/if}
{if $errore}<p class="error">{$errore|escape}</p>{/if}
<h2>Lezioni disponibili</h2>
{if !$lezioniDisponibili}<p>Nessuna lezione disponibile.</p>{else}
<table><tr><th>Data</th><th>Tipo</th><th></th></tr>
{foreach $lezioniDisponibili as $lezione}
<tr><td>{$lezione->getDataOra()->format('d/m/Y H:i')}</td>
<td>{if $lezione instanceof \CamassoMedelago\DriveMeSafely\Entity\ELezioneTeoria}Teoria{else}Guida pratica{/if}</td>
<td><form method="post"><input type="hidden" name="lezione" value="{$lezione->getIdLezione()}"><input type="hidden" name="tipoLezione" value="{if $lezione instanceof \CamassoMedelago\DriveMeSafely\Entity\ELezioneTeoria}TEORIA{else}PRATICA{/if}"><button type="submit">Prenota</button></form></td></tr>
{/foreach}</table>{/if}
<h2>Storico prenotazioni</h2>
{foreach $storicoPrenotazioni as $prenotazione}
<p>{$prenotazione->getLezione()->getDataOra()->format('d/m/Y H:i')} - {$prenotazione->getStato()}</p>
{foreachelse}<p>Nessuna prenotazione.</p>{/foreach}
</main>
</body></html>
