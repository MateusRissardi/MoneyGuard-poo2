<?php

$user_name = $_SESSION['user_name'];

require_once '../app/core/Database.php';
require_once '../app/model/Group.php';


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login?error=auth");
    exit;
}

$user_name = $_SESSION['user_name'];

require_once '../app/core/Database.php';
require_once '../app/model/Group.php';

if (!function_exists('getInitials')) {
    function getInitials($name) {
        $parts = explode(" ", trim($name));

        if (count($parts) === 1) {
            return strtoupper(substr($parts[0], 0, 1));
        }

        return strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
    }
}

try {
    $db_sidebar = Database::getInstance()->getConnection();
    $groupModel_sidebar = new Group($db_sidebar);
    $sidebar_grupos = $groupModel_sidebar->getGroupsByUser($_SESSION['user_id']);
} catch (PDOException $e) {
    $sidebar_grupos = [];
    error_log("Erro ao buscar grupos para o sidebar: " . $e->getMessage());
}
$current_url = $_GET['url'] ?? ''; 

$rotas_sem_header = [
    'transaction',       
    'recent_activities', 
    'groups',            
    'settings'           
];

$exibir_header = !in_array($current_url, $rotas_sem_header) && !str_starts_with($current_url, 'group/settings/');
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyGuard</title>
    <base href="/MoneyGuard-poo2/public/">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php
    require_once '../views/components/sidebar.php'
        ?>
    <div class="main-content-wrapper">

<?php
        if ($exibir_header):
            ?>
            <header>
                <?php if (isset($grupo) && is_array($grupo) && str_starts_with($current_url, 'group/view/')): ?>
                    <h3 style="color: var(--color-text) !important">Painel do Grupo:
                        <?php echo htmlspecialchars($grupo['nome_grupo']); ?>
                    </h3>
                <?php else: ?>
                    <h2 style="color: var(--color-primary) !important">MoneyGuard</h2>
                <?php endif; ?>

                <nav>
                    <h5 style="font-weight: normal;">Olá, <?php echo htmlspecialchars($user_name); ?>!</h5>
                </nav>
            </header>
            <hr>
            <?php
        endif;
        ?>
        <main>