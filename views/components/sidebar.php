<?php
$current_url = $_GET['url'] ?? 'dashboard';
?>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <button class="sidebar-toggle" id="sidebarToggle">
            <img src="images/icon-logo.svg" alt="MoneyGuard Logo" class="sidebar-logo">
        </button>
    </div>

    <nav class="sidebar-nav">
        <a href="dashboard" class="nav-link <?php if ($current_url == 'dashboard' || str_starts_with($current_url, 'group/')) echo 'active'; ?>">
            <i class="bi bi-house-door-fill"></i>
            <span>Dashboard</span>
        </a>
        <a href="transaction" class="nav-link <?php if ($current_url == 'transaction') echo 'active'; ?>">
            <i class="bi bi-cash-coin"></i>
            <span>Transação</span>
        </a>
        <a href="recent_activities" class="nav-link" <?php if ($current_url == 'recent_activities') echo 'active'; ?>">
            <i class="bi bi-arrow-counterclockwise"></i>
            <span>Atividades Recentes</span>
        </a>
        <a href="groups" class="nav-link <?php
                                            // Fica ativo se estiver na página 'groups' OU dentro de um 'group/view/'
                                            if ($current_url == 'groups' || str_starts_with($current_url, 'group/')) echo 'active';
                                            ?>">
            <i class="bi bi-people-fill"></i>
            <span>Grupos</span>
        </a>
        <div class="sidebar-submenu">
            <?php
            // A variável $sidebar_grupos vem do header.php
            if (!empty($sidebar_grupos)):
                foreach ($sidebar_grupos as $grupo):
                    // Verifica se este é o grupo ativo sendo visualizado
                    $is_active_group = ($current_url == 'group/view/' . $grupo['id_grupo']);
            ?>
                    <a href="group/view/<?php echo $grupo['id_grupo']; ?>" class="nav-link-sub <?php if ($is_active_group) echo 'active-sub'; ?>">
                        <i class="bi bi-circle"></i> <!-- Ícone de círculo como na imagem -->
                        <span><?php echo htmlspecialchars($grupo['nome_grupo']); ?></span>
                    </a>
            <?php
                endforeach;
            endif;
            ?>

            <!-- Link para Criar Novo Grupo (leva para a página de grupos) -->
            <a href="groups#create" class="nav-link-sub"> <!-- O #create pode ser usado para abrir o modal direto -->
                <i class="bi bi-plus-circle"></i> <!-- Ícone de mais como na imagem -->
                <span>Criar um novo grupo</span>
            </a>
        </div>
        <a href="#" class="nav-link">
            <i class="bi bi-gear-fill"></i>
            <span>Configuração</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout" class="nav-link">
            <i class="bi bi-box-arrow-right"></i>
            <span>Sair</span>
        </a>
    </div>
</aside>