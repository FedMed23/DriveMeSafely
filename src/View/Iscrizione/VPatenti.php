<!DOCTYPE html>
<html>
<head>
    <title>Seleziona Patente</title>
</head>
<body>

<h1>Scegli la patente</h1>

<?php if (!isset($patenti) || empty($patenti)): ?>
    <p>Nessuna patente disponibile</p>
<?php else: ?>

    <?php foreach ($patenti as $patente): ?>
        <div>
            <p>Tipo: <?= $patente->getTipo() ?></p>

            <form method="POST" action="/confermaDati.php">
                <input type="hidden" name="idPa" value="<?= $patente->getId() ?>">
                <button type="submit">Seleziona</button>
            </form>
        </div>
        <hr>
    <?php endforeach; ?>

<?php endif; ?>

</body>
</html>