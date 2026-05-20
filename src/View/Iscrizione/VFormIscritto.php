<!DOCTYPE html>
<html>
<head>
    <title>Iscrizione</title>
</head>
<body>

<h1>Dati Iscritto</h1>

<form method="POST" action="/inserisciDati.php">

    Nome: <input type="text" name="nome"><br>
    Cognome: <input type="text" name="cognome"><br>
    Email: <input type="email" name="email"><br>
    Username: <input type="text" name="username"><br>
    Password: <input type="password" name="password"><br>

    Codice fiscale: <input type="text" name="codiceFiscale"><br>
    Data nascita: <input type="date" name="dataNascita"><br>
    Luogo nascita: <input type="text" name="luogoNascita"><br>
    Indirizzo: <input type="text" name="indirizzo"><br>
    Telefono: <input type="text" name="numeroTelefono"><br>

    <button type="submit">Continua</button>

</form>

</body>
</html>
