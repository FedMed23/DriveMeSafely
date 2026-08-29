<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DriveMeSafely</title>
    <link rel="stylesheet" href="{$request.contextPath}/css/style.css?v=2">
</head>
<body>
    <header>
        <h1>DriveMeSafely</h1>
        <nav><a href="{$request.contextPath}/">Home</a></nav>
    </header>
    <main class="auth-page">
        <section class="auth-card">
        <h2>Accedi alla tua area riservata</h2>
        {if $errore}
            <div class="auth-alert" role="alert">
                {$errore|escape}
            </div>
        {/if}
        <form class="auth-form" action="{$request.contextPath}/home/login" method="POST">
            <div class="auth-field">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username"
                       value="{$oldData.username|default:''|escape}"
                       autocomplete="username" required>
            </div>
            <div class="auth-field">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password"
                       autocomplete="current-password" required>
            </div>
            <div class="auth-captcha">
                <label for="captcha">
                    Controllo di sicurezza:
                    <strong>Quanto fa {$num1} + {$num2}?</strong>
                </label>
                <input type="number" id="captcha" name="captcha"
                       inputmode="numeric" required>
            </div>
            <button class="btn auth-submit" type="submit">Accedi</button>
        </form>
        </section>
    </main>
</body>
</html>
