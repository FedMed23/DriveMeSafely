<!DOCTYPE html>
<html>

<head>
    <title>Inserisci Carta</title>
</head>

<body>

<h1>Inserisci i dati della carta</h1>

<form method="POST"
      action="index.php?page=confermaPagamento">

    <input type="hidden"
           name="idPagamento"
           value="{$idPagamento}">

    <label>Nome intestatario:</label>
    <input type="text"
           name="nome"
           required>

    <br><br>

    <label>Cognome intestatario:</label>
    <input type="text"
           name="cognome"
           required>

    <br><br>

    <label>Numero carta:</label>
    <input type="text"
           name="numero"
           required>

    <br><br>

    <label>Data scadenza:</label>
    <input type="date"
           name="scadenza"
           required>

    <br><br>

    <button type="submit">
        Conferma pagamento
    </button>

</form>

</body>
</html>
