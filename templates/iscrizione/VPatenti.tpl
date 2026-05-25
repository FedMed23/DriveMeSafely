<!DOCTYPE html>
<html>

<head>
    <title>Seleziona Patente</title>
</head>

<body>

<h1>Seleziona la patente</h1>

{foreach $patenti as $patente}

    <div>

        <h3>Patente {$patente->getTipo()}</h3>

        <form method="POST"
              action="index.php?page=inserisciDati">
            
            <input type="hidden" 
                   name="page" 
                   value="inserisciDati">

            <input type="hidden"
                   name="idPa"
                   value="{$patente->getId()}">

            <button type="submit">
                Seleziona
            </button>

        </form>

    </div>

    <hr>

{/foreach}

</body>
</html>
