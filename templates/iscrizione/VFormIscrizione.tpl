<!DOCTYPE html>
<html>

<head>
    <title>Inserisci Dati</title>
</head>

<body>

<h1>Inserisci i tuoi dati</h1>

<form method="POST"
      action="index.php?page=confermaIscrizione">

    <input type="hidden"
           name="idPa"
           value="{$idPa}">

    <label>Nome:</label>
    <input type="text" name="nome" required>
    <br><br>

    <label>Cognome:</label>
    <input type="text" name="cognome" required>
    <br><br>

    <label>Email:</label>
    <input type="email" name="email" required>
    <br><br>

    <label>Username:</label>
    <input type="text" name="username" required>
    <br><br>

    <label>Password:</label>
    <input type="password" name="password" required>
    <br><br>

    <label>Codice fiscale:</label>
    <input type="text" name="codiceFiscale" required>
    <br><br>

    <label>Data nascita:</label>
    <input type="date" name="dataNascita" required>
    <br><br>

    <label>Luogo nascita:</label>
    <input type="text" name="luogoNascita" required>
    <br><br>

    <label>Indirizzo:</label>
    <input type="text" name="indirizzo" required>
    <br><br>

    <label>Telefono:</label>
    <input type="text" name="numeroTelefono" required>
    <br><br>

    <button type="submit">
        Conferma iscrizione
    </button>

</form>

</body>
</html>
