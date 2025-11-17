<?php
require_once '../views/components/header.php';
require_once '../app/model/Group.php';
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
        background: var(--sidebar-bg);
        border: 1px solid var(--sidebar-border);
        border-radius: 12px;
        padding: 1.5rem 2rem;
        margin-bottom: 2rem;
    }

    .config-section:last-of-type {
        border-bottom: 1px solid var(--sidebar-border);
    }

    .config-section h4 {
        color: var(--color-text);
        font-size: 1.2rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .config-subtitle {
        color: var(--color-text-secondary);
        font-size: 0.9rem;
        margin-bottom: 1rem;
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
        border: 1px solid var(--sidebar-border);
        border-radius: 8px;
        padding: 0.8rem 1rem;
        color: var(--color-text);
        font-weight: 500;
        font-family: 'Poppins', sans-serif;
    }

    .config-input:read-only {
        color: var(--color-text-secondary);
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
        background-image: url('https://placehold.co/40x40/333333/F0B90B?text=MG');
        background-size: cover;
    }

    .config-member-item .member-details {
        flex-grow: 1;
        font-weight: 500;
        color: var(--color-text);
    }

    .member-tag {
        display: block;
        font-size: 0.8rem;
        color: var(--color-text-secondary);
        font-weight: 400;
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

    .delete-section .btn-danger {
        background: #E84545 !important;
        border-color: #E84545 !important;
        width: 100%;
        max-width: 200px;
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
        <a href="../../group/view/<?php echo $grupo['id_grupo']; ?>" class="back-link" title="Voltar ao Grupo">
            <i class="bi bi-arrow-left-circle-fill"></i>
        </a>
        <h3 class="config-header-title"><?php echo htmlspecialchars($grupo['nome_grupo']); ?>: Configuração</h3>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'group_updated'): ?>
        <div class="alert alert-success" style="margin-bottom: 1rem;">
            <i class="fa-solid fa-check"></i>
            Nome do grupo atualizado com sucesso!
        </div>
    <?php endif; ?>


    <?php if ($grupo['id_admin'] == $meu_id): ?>

        <form action="../../group/update" method="POST" class="config-section">
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
            <h4>Gerenciar membros do grupos</h4>

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
                        <div class="member-avatar"></div> 
                        <div class="member-details">
                            <?php echo htmlspecialchars($membro['nome']); ?>
                            <?php if ($membro['id_usuario'] == $meu_id): ?>
                                <span class="member-tag">(Você)</span>
                            <?php endif; ?>
                            <?php if ($membro['id_usuario'] == $grupo['id_admin']): ?>
                                <span class="member-tag admin">Admin do Grupo</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($membro['id_usuario'] != $meu_id): // Admin não pode remover a si mesmo ?>
                            <form action="../../group/remove_member" method="POST" style="display: inline;"
                                onsubmit="return confirm('Tem certeza que deseja remover <?php echo htmlspecialchars($membro['nome']); ?>? (MSG32)');">
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

        <div class="config-section delete-section">
            <h4>Excluir grupo</h4>
            <p class="config-subtitle">Uma vez excluído, um grupo não pode ser recuperado.</p>
            <form action="../../group/delete" method="POST" style="display: inline-block;"
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
                        <div class="member-avatar"></div> 
                        <div class="member-details">
                            <?php echo htmlspecialchars($membro['nome']); ?>
                            <?php if ($membro['id_usuario'] == $meu_id): ?>
                                <span class="member-tag">(Você)</span>
                            <?php endif; ?>
                            <?php if ($membro['id_usuario'] == $grupo['id_admin']): ?>
                                <span class="member-tag admin">Admin do Grupo</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="config-section delete-section">
            <h4>Sair do grupo</h4>
            <p class="config-subtitle">Você pode sair deste grupo a qualquer momento.</p>
            <form action="../../group/remove_member" method="POST"
                onsubmit="return confirm('Tem certeza que deseja sair deste grupo?');">
                <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">
                <input type="hidden" name="id_membro" value="<?php echo $meu_id; ?>">
                <button type="submit" class="btn btn-danger">Sair do Grupo</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="config-footer">
        <a href="../../group/view/<?php echo $grupo['id_grupo']; ?>" class="btn btn-secondary"
            style="background: #333; border: 1px solid #555;">
            Cancelar
        </a>
        <a href="../../group/view/<?php echo $grupo['id_grupo']; ?>" class="btn btn-primary">
            Salvar
        </a>
    </div>
</div>


<div class="modal-overlay" id="generateCodeModal">
    <div class="modal-content">
        <div class="modal-header" style="justify-content: space-between; flex-direction: row; align-items: center;">
            <h4 class="mb-2">Gerar código de acesso!</h4>
            <button type="button" class="modal-close" onclick="closeModal('generateCodeModal')"
                style="font-size: 2rem; color: var(--color-text-secondary);">&times;</button>
        </div>
        <p>Compartilhe o código com as pessoas que você vai dividir!</p>

        <div class="modal-body" style="justify-content: flex-start;">
            <div class="form-group" style="width: 100%;">
                <label for="invite_code_display">Código de acesso</label>
                <div class="inputBtn">
                    <div class="input-wrapper liquid-glass" style="flex-grow: 1;">
                        <input type="text" id="invite_code_display" value="Gerando..." readonly
                            style="background: transparent; border: none; color: var(--color-text-secondary); font-weight: bold;">
                    </div>
                    <button id="copyCodeBtn" type="button" class="btn btn-primary"
                        onclick="copyToClipboard()">Copiar</button>
                </div>
            </div>
        </div>

        <div id="generateCodeError" class="alert alert-error" style="display: none; margin-top: 1rem;"></div>
    </div>
</div>


<script>
    // Funções de Modal (necessárias para o modal de Gerar Código)
    function openModal(modalId) {
        closeModal('generateCodeModal'); // Fecha qualquer modal aberto
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
    window.onclick = function (event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('visible');
        }
    }

    // JavaScript para o Modal de Gerar Código
    async function showGenerateCodeModal(groupId) {
        const modal = document.getElementById('generateCodeModal');
        const inputDisplay = document.getElementById('invite_code_display');
        const errorDisplay = document.getElementById('generateCodeError');
        const copyBtn = document.getElementById('copyCodeBtn');

        // Reseta o modal para o estado de "carregando"
        inputDisplay.value = "Gerando...";
        copyBtn.disabled = true;
        copyBtn.innerText = "Copiar";
        errorDisplay.style.display = 'none';

        // Abre o modal
        openModal('generateCodeModal');

        try {
            // CORREÇÃO DE CAMINHO: Precisa subir DOIS níveis (de 'group/settings/')
            const response = await fetch('../../group/generate_code/' + groupId, {
                method: 'POST'
            });

            if (!response.ok) {
                throw new Error('Falha na rede ao tentar gerar o código.');
            }

            const data = await response.json(); // Lê a resposta JSON do controller

            if (data.success) {
                // SUCESSO!
                const newCode = data.code; // Corrigido de data.new_code para data.code
                inputDisplay.value = newCode;
                copyBtn.disabled = false;
                copyBtn.setAttribute('data-code', newCode);
                copyBtn.innerText = "Copiar";

                // Atualiza o display do código na página de settings (sem recarregar)
                const configCodeDisplay = document.getElementById('config-invite-code-display');
                if (configCodeDisplay) {
                    configCodeDisplay.value = newCode;
                }

            } else {
                throw new Error(data.error || 'Erro desconhecido ao gerar o código.');
            }

        } catch (error) {
            inputDisplay.value = "Erro!";
            errorDisplay.innerText = error.message;
            errorDisplay.style.display = 'block';
        }
    }

    // Função para o botão "Copiar"
    function copyToClipboard() {
        const copyBtn = document.getElementById('copyCodeBtn');
        const codeToCopy = copyBtn.getAttribute('data-code');

        if (codeToCopy) {
            // Usa a API de Clipboard (mais moderna e segura)
            if (navigator.clipboard) {
                 navigator.clipboard.writeText(codeToCopy).then(() => {
                    copyBtn.innerText = "Copiado!";
                    setTimeout(() => { copyBtn.innerText = "Copiar"; }, 2000);
                }).catch(err => {
                    console.error('Falha ao copiar com API: ', err);
                    oldCopyCommand(codeToCopy); // Fallback
                });
            } else {
                oldCopyCommand(codeToCopy); // Fallback para execCommand
            }
        }
    }
    
    // Fallback para document.execCommand
    function oldCopyCommand(codeToCopy) {
        const tempInput = document.createElement('input');
        tempInput.value = codeToCopy;
        document.body.appendChild(tempInput);
        tempInput.select();
        tempInput.setSelectionRange(0, 99999); // Para mobile

        try {
            document.execCommand('copy');
            copyBtn.innerText = "Copiado!";
            setTimeout(() => { copyBtn.innerText = "Copiar"; }, 2000);
        } catch (err) {
            console.error('Falha ao copiar com execCommand: ', err);
        }
        document.body.removeChild(tempInput);
    }
</script>
<?php
require_once '../views/components/footer.php';
?>