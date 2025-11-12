<?php
require_once '../views/components/header.php';
?>

<div class="auth-messages" style="max-width: 600px; margin-bottom: 1rem;">
    <?php if (isset($error)): ?>
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
            if ($_GET['status'] == 'group_created')
                echo "Grupo criado com sucesso! (MSG04)";
            if ($_GET['status'] == 'group_joined')
                echo "Você entrou no grupo com sucesso! (MSG34)";
            ?>
        </div>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="noGroupsModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Bem-vindo!</h3>
        </div>
        <div class="modal-body">
            <p>O que você gostaria de fazer?</p>
        </div>
        <div class="modal-footer" style="justify-content: space-around;">
            <button class=" btn btn-modal" style="flex: 1;" onclick="showModal('createGroupModal')">
                <i class="bi bi-people-fill"></i> Criar Novo Grupo
            </button>
            <button class="btn btn-modal" style="flex: 1;" onclick="showModal('joinGroupModal')">
                <i class="bi bi-key"></i> Entrar com Código
            </button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="createGroupModal">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="mb-2">Criar Novo Grupo</h4>
            <p>Crie um grupo para gerenciar despesas com sues amigos!</p>
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
            <p>Partice de grupos para gerenciar despesas com seus amigos!</p>
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

<script>
    function showModal(modalId) {
        closeModal('noGroupsModal');
        closeModal('createGroupModal');
        closeModal('joinGroupModal');

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
        <?php if (isset($grupos) && empty($grupos)): ?>
            // Se não há grupos, força a abertura do modal principal
            showModal('noGroupsModal');
        <?php endif; ?>

        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) {

                    // CORREÇÃO: Não deixa fechar o modal 'noGroupsModal'
                    // O usuário é FORÇADO a Criar ou Entrar em um grupo.
                    if (overlay.id !== 'noGroupsModal') {
                        closeModal(overlay.id);
                    }
                }
            });
        });
    });
</script>