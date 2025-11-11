<?php
require_once '../views/components/auth_header.php';
?>

<div class="auth-container">

    <section class="auth-form-section">
        <div class="auth-form-wrapper">
            <img src="images/logo.svg" alt="MoneyGuard Logo" class="auth-logo">

            <div class="content-login">
                <p class="auth-title">Bem vindo de volta!</p>

                <form action="login" method="POST" class="auth-form">

                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-user input-icon"></i>
                            <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="senha">Senha</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
                            <i class="fa-solid fa-eye-slash input-icon-toggle input-icon me-0" id="toggleIcon"
                                onclick="togglePassword()"></i>
                        </div>
                    </div>

                    <!-- <div class="form-options">
                        <a href="#" class="form-link">Esqueceu a senha? <span
                                style="color: var(--color-primary)">Recupere</span></a>
                    </div> -->

                    <button type="submit" class="btn btn-primary btn-block">Entrar</button>
                </form>

                <div class="form-options">
                    <a href="register" class="form-link">Não possui login? <span
                            style="color: var(--color-primary)">Cadastre-se</span></a>
                </div>

                <div class="auth-messages">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-error">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                        <div class="alert alert-success">
                            <i class="fa-solid fa-check"></i>
                            Usuário cadastrado com sucesso! Faça o login. (MSG08)
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="auth-brand-section">
        <div class="animation-wrapper">
            <div class="highlight-animation"></div>
            <img src="images/logo-centro.svg" class="coin-center" alt="Centro">
            <div class="orbit outer-orbit">
                <img src="images/circulo-externo.svg" class="orbit-path" alt="Círculo externo">

                <div class="avatar" style="--top: -0%; --left: 50%;">
                    <img src="images/avatar-4.svg" alt="Avatar 4">
                </div>
                <div class="avatar" style="--top: 75%; --left: 10%;">
                    <img src="images/avatar-5.svg" alt="Avatar 5">
                </div>
                <div class="avatar" style="--top: 90%; --left: 80%;">
                    <img src="images/avatar-6.svg" alt="Avatar 6">
                </div>
            </div>

            <div class="orbit inner-orbit">
                <img src="images/circulo-interno.svg" class="orbit-path" alt="Círculo interno">

                <div class="avatar" style="--top: 0%; --left: 50%;">
                    <img src="images/avatar-1.svg" alt="Avatar 1">
                </div>
                <div class="avatar" style="--top: 75%; --left: 10%;">
                    <img src="images/avatar-2.svg" alt="Avatar 2">
                </div>
                <div class="avatar" style="--top: 85%; --left: 80%;">
                    <img src="images/avatar-3.svg" alt="Avatar 3">
                </div>
            </div>
        </div>
        <div class="auth-brand-text">
            <h3>O jeito mais fácil de</h3>
            <h3>compartilhar <span class="highlight">contas</span></h3>
            <p>Dividir contas com seus amigos nunca foi tão fácil</p>
        </div>
    </section>
</div>

<?php
require_once '../views/components/auth_footer.php';
?>