<!DOCTYPE html>
<html>

<head>
    <title>Pagamenti</title>
</head>

<body>

<h1>Lista Pagamenti</h1>

{foreach $pagamenti as $pagamento}

    <div>

        <h3>Pagamento #{$pagamento->getId()}</h3>

        <p>
            Stato:
            {$pagamento->getStato()}
        </p>

        <p>
            Data:
            {$pagamento->getData()->format('d/m/Y')}
        </p>

        <form method="POST"
              action="index.php?page=inserisciCarta">

            <input type="hidden"
                   name="idPagamento"
                   value="{$pagamento->getId()}">

            <button type="submit">
                Paga
            </button>

        </form>

    </div>

    <hr>

{/foreach}

</body>
</html>
