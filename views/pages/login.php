<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MoneyGuard</title>
</head>

<body>

    <h2>Acesse sua conta (CDU02)</h2>
    <p>Implementa HU002 (Autenticar usuário)</p>

    <form action="login" method="POST">

        <?php if (isset($error)): ?>
            <div style="color: red; border: 1px solid red; padding: 10px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div style="color: green; border: 1px solid green; padding: 10px;">
                Usuário cadastrado com sucesso! Faça o login. (MSG08)
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success_joined'): ?>
            <div style="color: green; border: 1px solid green; padding: 10px;">
                Usuário cadastrado e adicionado ao grupo com sucesso! Faça o login. (MSG34)
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success_join_failed'): ?>
            <div style="color: #cc8a00; border: 1px solid #cc8a00; padding: 10px;">
                Usuário cadastrado, mas falha ao entrar no grupo:
                <b><?php echo htmlspecialchars($_GET['join_error']); ?></b> (MSG21 ou MSG31)
            </div>
        <?php endif; ?>
        
        <div>
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <br>
        <div>
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>
        </div>
        <br>
        <button type="submit">Entrar (HU002)</button>
    </form>

    <p>Não tem uma conta? <a href="register">Cadastre-se</a></p>

</body>

</html>