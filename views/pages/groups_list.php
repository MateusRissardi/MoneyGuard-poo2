<?php
// Este arquivo agora busca seus próprios dados, pois é uma página
// (O header.php busca os grupos apenas para o sidebar)

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Requerimentos para esta página
require_once '../app/core/Database.php';
require_once '../app/model/Group.php';

$grupos = [];
$error = null;

if (!isset($_SESSION['user_id'])) {
    header("Location: login?error=auth");
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $groupModel = new Group($db);
    $grupos = $groupModel->getGroupsByUser($_SESSION['user_id']);
} catch (PDOException $e) {
    $error = "Erro ao carregar grupos: " . $e->getMessage();
}

// Inclui o header (que por sua vez inclui o sidebar)
require_once '../views/components/header.php';
?>

<!-- Mensagens de Erro/Sucesso (copiadas do dashboard.php) -->
<div class="auth-messages" style="max-width: 600px; margin-bottom: 1rem;">
    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['status'])): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-check"></i>
            <?php
            if ($_GET['status'] == 'group_created') echo "Grupo criado com sucesso! (MSG04)";
            if ($_GET['status'] == 'group_joined') echo "Você entrou no grupo com sucesso! (MSG34)";
            ?>
        </div>
    <?php endif; ?>
</div>


<!-- Botões Principais -->
<div style="display: flex; gap: 20px; margin-bottom: 2rem;">
    <button class="btn btn-primary" onclick="showModal('createGroupModal')">
        <i class="bi bi-people-fill"></i> Criar Novo Grupo
    </button>
    <button class="btn btn-secondary" style="background-color: #333;" onclick="showModal('joinGroupModal')">
        <i class="bi bi-key"></i> Entrar com Código
    </button>
</div>


<!-- Lista de Grupos -->
<h2>Meus Grupos</h2>
<div class="group-list-container">
    <?php if (empty($grupos)): ?>
        <p>Você ainda não participa de nenhum grupo. Crie um ou entre em um grupo usando um código de convite.</p>
        
        <!-- Script para mostrar o modal de 'criar' se não houver grupos -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Verifica se um modal específico não está sendo aberto pela URL
                if(window.location.hash !== '#create' && window.location.hash !== '#join') {
                    showModal('createGroupModal');
                }
            });
        </script>

    <?php else: ?>
        <!-- Exibe os grupos como cards (Exemplo) -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
            <?php foreach ($grupos as $grupo): ?>
                <a href="group/view/<?php echo $grupo['id_grupo']; ?>" class="card-link">
                    <div class="card-options" style="text-align: left; padding: 1.5rem;">
                        <h4 style="color: var(--color-primary);"><?php echo htmlspecialchars($grupo['nome_grupo']); ?></h4>
                        <p style="color: var(--color-text-secondary); margin-top: 10px;">
                            <?php echo ($grupo['id_admin'] == $_SESSION['user_id']) ? 'Você é o admin' : 'Membro'; ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>


<!-- Modais (copiados do dashboard.php) -->

<div class="modal-overlay" id="createGroupModal">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="mb-2">Criar Novo Grupo</h4>
            <p>Crie um grupo para gerenciar despesas com seus amigos!</p>
        </div>
        <form action="group/create" method="POST">
            <div class="modal-body">
                <div class="form-group">
                    <label for="nome_grupo">Nome do Grupo:</label>
                    <div class="inputBtn">
                        <div class="input-wrapper liquid-glass">
                            <input type="text" id="nome_grupo" name="nome_grupo" placeholder="Ex: Contas do Apê"
                                required>
                        </div>
                        <button style="text-wrap:nowrap" type="submit" class="btn btn-primary">Criar Grupo</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="joinGroupModal">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="mb-2">Entrar em um Grupo</h4>
            <p>Participe de grupos para gerenciar despesas com seus amigos!</p>
        </div>
        <form action="group/join_with_code" method="POST">
            <div class="modal-body">
                <div class="form-group">
                    <label for="codigo_convite">Código de Convite:</label>
                    <div class="inputBtn">
                        <div class="input-wrapper liquid-glass">
                            <i class="fa fa-key input-icon"></i>
                            <input type="text" id="codigo_convite" name="codigo_convite" placeholder="MG-XXXXX"
                                required>
                        </div>
                        <button type="submit" class="btn btn-primary">Entrar</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


<?php
require_once '../views/components/footer.php';
?>

<!-- Script dos Modais (copiado do dashboard.php) -->
<script>
    function showModal(modalId) {
        // Fecha todos os modais abertos
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.classList.remove('visible');
        });

        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('visible');
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('visible');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Lógica para fechar modal ao clicar fora
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) {
                    closeModal(overlay.id);
                }
            });
        });
        
        // Abre o modal de criação se o hash #create estiver na URL
        if(window.location.hash === '#create') {
            showModal('createGroupModal');
        }
        // Abre o modal de entrar se o hash #join estiver na URL
        if(window.location.hash === '#join') {
            showModal('joinGroupModal');
        }
    });
</script>