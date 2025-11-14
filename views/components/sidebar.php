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

        <a href="groups" class="nav-link <?php if ($current_url == 'groups') echo 'active'; ?>">
            <i class="bi bi-people-fill"></i>
            <span>Grupos</span>
        </a>

        <div class="sidebar-submenu" style="<?php if (str_starts_with($current_url, 'group/')) echo 'display: flex;'; ?>">
            <?php
            if (!empty($sidebar_grupos)):
                foreach ($sidebar_grupos as $sidebar_grupo): 
                    $is_active_group = ($current_url == 'group/view/' . $sidebar_grupo['id_grupo']);
            ?>
                    <a href="group/view/<?php echo $sidebar_grupo['id_grupo']; ?>" class="nav-link-sub <?php if ($is_active_group) echo 'active-sub'; ?>">
                        <i class="bi bi-circle"></i> 
                        <span><?php echo htmlspecialchars($sidebar_grupo['nome_grupo']); ?></span>
                    </a>
            <?php
                endforeach;
            endif;
            ?>

            <a href="groups#create" class="nav-link-sub"> 
                <i class="bi bi-plus-circle"></i> 
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