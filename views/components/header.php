<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    // Redireciona de volta para a página de login com uma mensagem de erro
    header("Location: login?error=auth");
    exit;
}

$user_name = $_SESSION['user_name'];

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyGuard</title>
</head>

<body>
    <header>
        <h1>MoneyGuard</h1>
        <nav>
            <p>Olá, <?php echo htmlspecialchars($user_name); ?>!</p>
            <a href="dashboard">Meus Grupos</a>
            <a href="logout">Sair (Logout)</a>
        </nav>
    </header>
    <hr>
    <main></main>