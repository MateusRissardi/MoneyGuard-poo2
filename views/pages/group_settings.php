<?php
require_once '../views/components/header.php';

?>
<style>
    .config-page-wrapper {
        max-width: 800px;
        margin: 0 auto;
        padding-bottom: 4rem;
    }

    .config-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .config-header-title {
        color: var(--color-text);
        font-size: 1.5rem;
        font-weight: 500;
        margin: 0;
    }

    .back-link {
        color: var(--color-text-secondary);
        font-size: 1.5rem;
        text-decoration: none;
    }

    .back-link:hover {
        color: var(--color-primary);
    }

    .config-section {
        background: var(--color-card-bg);
        border: 1px solid var(--color-border);
        border-radius: 12px;
        padding: 1.5rem 2rem;
        margin-bottom: 2rem;
    }

    .config-section h4 {
        color: var(--color-text);
        font-size: 1.2rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .config-subtitle {
        color: var(--color-text-secondary) !important;
        font-size: 16px;
        margin-bottom: 1rem !important;
    }

    .config-input-group {
        display: flex;
        gap: 1rem;
        align-items: center;
        max-width: 500px;
    }

    .config-input {
        flex-grow: 1;
        background-color: var(--color-background);
        border: 1px solid #444;
        border-radius: 8px;
        padding: 0.8rem 1rem;
        color: var(--color-text);
        font-weight: 500;
        font-family: 'Inter', sans-serif;
    }

    .config-member-list {
        margin-top: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .config-member-item {
        display: flex;
        align-items: center;
        background-color: var(--color-background);
        padding: 1rem;
        border-radius: 8px;
    }

    .config-member-item .member-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #333;
        margin-right: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #aaa;
    }

    .config-member-item .member-details {
        flex-grow: 1;
        font-weight: 500;
        color: var(--color-text);
    }

    .member-tag {
        font-size: 0.8rem;
        color: var(--color-text-secondary);
        margin-left: 10px;
    }

    .member-tag.admin {
        color: var(--color-primary);
    }

    .config-delete-btn {
        background: none;
        border: none;
        color: var(--color-text-secondary);
        font-size: 1.2rem;
        cursor: pointer;
    }

    .config-delete-btn:hover {
        color: #E84545;
    }

    .btn-danger {
        background: #E84545 !important;
        border-color: #E84545 !important;
        color: #fff !important;
    }

    .config-footer {
        margin-top: 1.5rem;
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
    }
</style>

<div class="config-page-wrapper">

    <div class="config-header">
        <h3 class="config-header-title"><?php echo htmlspecialchars($grupo['nome_grupo']); ?>: Configuração</h3>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'group_updated'): ?>
        <div class="alert alert-success" style="margin-bottom: 1rem;">
            <i class="fa-solid fa-check"></i> Nome do grupo atualizado com sucesso!
        </div>
    <?php endif; ?>

    <?php if ($grupo['id_admin'] == $meu_id): ?>

        <form action="<?php echo BASE_URL; ?>group/update" method="POST" class="config-section">
            <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">
            <h4>Detalhes do grupo</h4>
            <p class="config-subtitle">Nome do grupo</p>
            <div class="config-input-group">
                <input type="text" name="nome_grupo" id="group_name_input" class="config-input"
                    value="<?php echo htmlspecialchars($grupo['nome_grupo']); ?>" required>
                <button type="submit" class="btn btn-primary">Salvar</button>
            </div>
        </form>

        <div class="config-section">
            <h4>Gerenciar membros do grupo</h4>

            <p class="config-subtitle">Gere um novo código de acesso</p>
            <div class="config-input-group">
                <input type="text" class="config-input"
                    value="<?php echo htmlspecialchars($grupo['codigo_convite'] ?? 'Nenhum código gerado'); ?>"
                    id="config-invite-code-display" readonly>
                <button type="button" class="btn btn-primary"
                    onclick="showGenerateCodeModal(<?php echo $grupo['id_grupo']; ?>)">Gerar</button>
            </div>

            <div class="config-member-list">
                <?php foreach ($membros as $membro): ?>
                    <div class="config-member-item">
                        <div class="member-avatar"><i class="bi bi-person-fill"></i></div>
                        <div class="member-details">
                            <?php echo htmlspecialchars($membro['nome']); ?>
                            <?php if ($membro['id_usuario'] == $meu_id): ?>
                                <span class="member-tag">(Você)</span>
                            <?php endif; ?>
                            <?php if ($membro['id_usuario'] == $grupo['id_admin']): ?>
                                <span class="member-tag admin">Admin</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($membro['id_usuario'] != $meu_id): ?>
                            <form action="<?php echo BASE_URL; ?>group/remove_member" method="POST" style="display: inline;"
                                onsubmit="return confirm('Tem certeza que deseja remover <?php echo htmlspecialchars($membro['nome']); ?>?');">
                                <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">
                                <input type="hidden" name="id_membro" value="<?php echo $membro['id_usuario']; ?>">
                                <button type="submit" class="config-delete-btn" title="Remover Membro">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="config-section delete-section" style="border-color: #E84545;">
            <h4 style="color: #E84545;">Excluir grupo</h4>
            <p class="config-subtitle">Uma vez excluído, um grupo não pode ser recuperado.</p>
            <form action="<?php echo BASE_URL; ?>group/delete" method="POST" style="display: inline-block;"
                onsubmit="return confirm('Tem certeza que deseja excluir este grupo?');">
                <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">
                <button type="submit" class="btn btn-danger">Excluir Grupo</button>
            </form>
        </div>

    <?php else: ?>
        <div class="config-section">
            <h4>Membros do grupo</h4>
            <p class="config-subtitle">Você não é o admin deste grupo.</p>
            <div class="config-member-list">
                <?php foreach ($membros as $membro): ?>
                    <div class="config-member-item">
                        <div class="member-avatar"><i class="bi bi-person-fill"></i></div>
                        <div class="member-details">
                            <?php echo htmlspecialchars($membro['nome']); ?>
                            <?php if ($membro['id_usuario'] == $meu_id)
                                echo '<span class="member-tag">(Você)</span>'; ?>
                            <?php if ($membro['id_usuario'] == $grupo['id_admin'])
                                echo '<span class="member-tag admin">Admin</span>'; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="config-section delete-section">
            <h4 style="color: #E84545;">Sair do grupo</h4>
            <p class="config-subtitle">Você pode sair deste grupo a qualquer momento.</p>
            <form action="<?php echo BASE_URL; ?>group/remove_member" method="POST"
                onsubmit="return confirm('Tem certeza que deseja sair deste grupo?');">
                <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">
                <input type="hidden" name="id_membro" value="<?php echo $meu_id; ?>">
                <button type="submit" class="btn btn-danger">Sair do Grupo</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="config-footer">
        <a href="<?php echo BASE_URL; ?>group/view/<?php echo $grupo['id_grupo']; ?>" class="btn btn-secondary"
            style="background: #333; border: 1px solid #555;">
            Voltar
        </a>
    </div>
</div>

<div class="modal-overlay" id="generateCodeModal">
    <div class="modal-content">
        <div class="modal-header" style="display: flex; justify-content: space-between;">
            <h4 class="mb-2">Gerar código</h4>
            <span class="modal-close" onclick="closeModal('generateCodeModal')">&times;</span>
        </div>
        <p>Compartilhe o código com novos membros.</p>
        <div class="inputBtn" style="display: flex; gap: 10px;">
            <input type="text" id="invite_code_display" value="Gerando..." readonly class="config-input">
            <button id="copyCodeBtn" type="button" class="btn btn-primary" onclick="copyToClipboard()">Copiar</button>
        </div>
        <div id="generateCodeError" class="alert alert-error" style="display: none;"></div>
    </div>
</div>

<script>
    const BASE_URL = "<?php echo BASE_URL; ?>";

    function openModal(modalId) {
        document.getElementById(modalId).classList.add('visible');
    }
    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('visible');
    }
    window.onclick = function (event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('visible');
        }
    }

    async function showGenerateCodeModal(groupId) {
        const modal = document.getElementById('generateCodeModal');
        const inputDisplay = document.getElementById('invite_code_display');
        const errorDisplay = document.getElementById('generateCodeError');
        const copyBtn = document.getElementById('copyCodeBtn');

        inputDisplay.value = "Gerando...";
        copyBtn.disabled = true;
        errorDisplay.style.display = 'none';
        openModal('generateCodeModal');

        try {
            const response = await fetch(BASE_URL + 'group/generate_code/' + groupId, { method: 'POST' });
            const data = await response.json();

            if (data.success) {
                inputDisplay.value = data.code;
                copyBtn.disabled = false;
                copyBtn.setAttribute('data-code', data.code);

                const pageInput = document.getElementById('config-invite-code-display');
                if (pageInput) pageInput.value = data.code;
            } else {
                throw new Error(data.error || 'Erro desconhecido.');
            }
        } catch (error) {
            inputDisplay.value = "Erro!";
            errorDisplay.innerText = error.message;
            errorDisplay.style.display = 'block';
        }
    }

    function copyToClipboard() {
        const code = document.getElementById('copyCodeBtn').getAttribute('data-code');
        navigator.clipboard.writeText(code).then(() => alert('Código copiado!'));
    }
</script>

<?php require_once '../views/components/footer.php'; ?>