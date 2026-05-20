<!DOCTYPE html>
<html>
<head>
    <title>Selezione Patente</title>
</head>
<body>

<h1>Scegli la patente</h1>

<?php foreach ($patenti as $patente): ?>
    <div>
        <p>Tipo: <?= $patente->getTipo() ?></p>
        <p>Prezzo: <?= $patente->getPrezzo() ?> €</p>

        <form method="POST" action="/confermaDati.php">
            <input type="hidden" name="idPa" value="<?= $patente->getId() ?>">
            <button type="submit">Seleziona</button>
        </form>
    </div>
    <hr>
<?php endforeach; ?>

</body>
</html>
