<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - MoneyGuard</title>
</head>

<body>

    <h2>Crie sua conta no MoneyGuard (CDU01)</h2>
    <p>Implementa HU001 (Realizar Cadastro) [cite: 579]</p>

    <form action="register" method="POST">

        <?php if (isset($error)): ?>
            <div style="color: red; border: 1px solid red; padding: 10px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div>
            <label for="nome">Nome Completo:</label>
            <input type="text" id="nome" name="nome" required>
        </div>
        <br>
        <div>
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <br>
        <div>
            <label for="data_nascimento">Data de Nascimento:</label>
            <input type="date" id="data_nascimento" name="data_nascimento" required>
        </div>
        <br>
        <div>
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>
        </div>
        <br>

        <div style="background: #f0f0f0; padding: 10px; border-radius: 5px;">
            <label for="codigo_convite">Código de Convite (Opcional):</label>
            <input type="text" id="codigo_convite" name="codigo_convite" placeholder="MG-XXXXX">
        </div>
        <br>
        <button type="submit">Cadastrar (HU001)</button>
    </form>

    <p>Já tem uma conta? <a href="login">Faça Login</a></p>

</body>

</html>