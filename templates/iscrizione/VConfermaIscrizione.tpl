<!DOCTYPE html>
<html>

<head>
    <title>Iscrizione completata</title>
</head>

<body>

<h1>Iscrizione completata con successo!</h1>

<p>
    Benvenuto {$iscritto->getNomeUtente()}
    {$iscritto->getCognomeUtente()}
</p>

<p>
    Patente selezionata:
    {$iscritto->getTipoPatente()->getTipo()}
</p>

<p>
    Ti abbiamo inviato una mail di conferma.
</p>

</body>
</html>
