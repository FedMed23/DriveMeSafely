<!DOCTYPE html>
<html>

<head>
    <title>Pagamento Confermato</title>
</head>

<body>

<h1>Pagamento completato con successo</h1>

<p>
    Il pagamento è stato registrato correttamente.
</p>

<p>
    Stato pagamento:
    {$pagamento->getStato()}
</p>

<p>
    Data:
    {$pagamento->getData()->format('d/m/Y')}
</p>

<a href="index.php?page=home">
    Torna alla home
</a>

</body>
</html>
