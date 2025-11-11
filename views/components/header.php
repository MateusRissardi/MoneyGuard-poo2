<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
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

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php require_once '../views/components/sidebar.php' ?>
    <div class="main-content-wrapper">

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