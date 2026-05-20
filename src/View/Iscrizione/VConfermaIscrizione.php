<!DOCTYPE html>
<html>
<head>
    <title>Conferma Iscrizione</title>
</head>
<body>

<h1>Iscrizione completata!</h1>

<p>Nome: <?= $iscritto->getNome() ?></p>
<p>Cognome: <?= $iscritto->getCognome() ?></p>
<p>Email: <?= $iscritto->getEmail() ?></p>

<a href="/home.php">Torna alla home</a>

</body>
</html>
