<?php
require_once '../views/components/header.php';

?>
<style>
    .config-container {
        max-width: 100%;
        margin: 0 auto;
        padding-bottom: 80px;
    }

    .config-title {
        font-size: 1.5rem;
        color: #fff;
        margin-bottom: 1.5rem;
        font-weight: 500;
    }

    .settings-card {
        background: #0f0f12;
        border: 1px solid #222;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }

    .section-label {
        color: #fff;
        font-size: 1.1rem;
        margin-bottom: 1rem;
        display: block;
    }

    .input-label {
        color: #888;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
        display: block;
    }

    .custom-input {
        width: 100%;
        background-color: #050507;
        border: 1px solid #333;
        color: #fff;
        padding: 12px 20px;
        border-radius: 50px; 
        font-size: 1rem;
        outline: none;
        transition: border-color 0.3s;
    }

    .custom-input:focus {
        border-color: var(--color-primary);
    }

    .custom-input[readonly] {
        color: #aaa;
        cursor: default;
    }

    .btn-action {
        background-color: var(--color-primary);
        color: #000;
        border: none;
        border-radius: 50px;
        padding: 10px 30px;
        font-weight: 600;
        cursor: pointer;
        text-transform: capitalize;
        transition: opacity 0.2s;
        white-space: nowrap;
    }

    .btn-action:hover {
        opacity: 0.9;
    }

    .btn-outline {
        background: transparent;
        border: 1px solid var(--color-primary);
        color: var(--color-primary);
        border-radius: 50px;
        padding: 10px 30px;
        font-weight: 600;
        cursor: pointer;
    }
    
    .btn-outline:hover {
        background: rgba(217, 164, 4, 0.1);
    }

    .form-row {
        display: flex;
        gap: 15px;
        align-items: flex-end;
        margin-bottom: 2.5rem;
    }

    .input-wrapper-flex {
        flex-grow: 1;
    }

    .member-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
        margin-top: 10px;
    }

    .member-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #222;
    }
    
    .member-card:last-child {
        border-bottom: none;
    }

    .member-info-left {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .avatar-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: #222;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #fff;
        overflow: hidden;
    }
    
    .avatar-circle img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .member-text h4 {
        margin: 0;
        font-size: 1rem;
        color: #fff;
        font-weight: normal;
    }

    .member-role {
        font-size: 0.85rem;
        color: var(--color-primary);
        margin-top: 2px;
    }

    .btn-delete {
        background: none;
        border: none;
        color: var(--color-primary); 
        font-size: 1.2rem;
        cursor: pointer;
        transition: color 0.2s;
    }
    
    .btn-delete:hover {
        color: #ff4444;
    }

    .card-footer {
        margin-top: 40px;
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        border-top: 1px solid #222;
        padding-top: 30px;
    }

    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
            align-items: stretch;
        }
        .btn-action {
            width: 100%;
        }
        .card-footer {
            flex-direction: column-reverse;
        }
        .card-footer button, .card-footer a {
            width: 100%;
            text-align: center;
        }
    }
</style>

<div class="config-container">
    <div class="config-header d-flex align-items-center gap-3 mb-4">
        <a href="group/view/<?php echo $grupo['id_grupo']; ?>" style="color: #888; font-size: 1.5rem;">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h2 class="config-title mb-0"><?php echo htmlspecialchars($grupo['nome_grupo']); ?>: Configuração</h2>
    </div>

    <div class="auth-messages mb-3">
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-check"></i> Grupo atualizado com sucesso!
            </div>
        <?php endif; ?>
    </div>

    <div class="settings-card">
        
        <h3 class="section-label">Detalhes do grupo</h3>
        
        <form id="formUpdateGroup" action="<?php echo BASE_URL; ?>group/update" method="POST">
            <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">
            
            <div class="form-row">
                <div class="input-wrapper-flex">
                    <label class="input-label">Nome do grupo</label>
                    <input type="text" name="nome_grupo" id="groupNameInput" 
                           class="custom-input" 
                           value="<?php echo htmlspecialchars($grupo['nome_grupo']); ?>" 
                           readonly>
                </div>
                <?php if($grupo['id_admin'] == $_SESSION['user_id']): ?>
                    <button type="button" id="btnEnableEdit" class="btn-action">Editar</button>
                <?php endif; ?>
            </div>
        </form>

        <h3 class="section-label">Gerenciar membros do grupo</h3>
        
        <div class="form-row">
            <div class="input-wrapper-flex">
                <label class="input-label">Gere um novo código de acesso</label>
                <input type="text" id="inviteCodeDisplay" 
                       class="custom-input" 
                       value="<?php echo htmlspecialchars($grupo['codigo_convite'] ?? '-----'); ?>" 
                       readonly>
            </div>
            <?php if($grupo['id_admin'] == $_SESSION['user_id']): ?>
                <button type="button" onclick="generateNewCode(<?php echo $grupo['id_grupo']; ?>)" class="btn-action">Gerar</button>
            <?php endif; ?>
        </div>

        <div class="member-list">
            <?php foreach ($membros as $membro): ?>
                <div class="member-card">
                    <div class="member-info-left">
                        <div class="avatar-circle">
                             <?= getInitials($membro['nome']) ?>
                        </div>
                        
                        <div class="member-text">
                            <h4>
                                <?php echo htmlspecialchars($membro['nome']); ?>
                                <?php if ($membro['id_usuario'] == $_SESSION['user_id']) echo ' (Você)'; ?>
                            </h4>
                            
                            <?php if ($membro['id_usuario'] == $grupo['id_admin']): ?>
                                <p class="member-role">Admin do Grupo</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($grupo['id_admin'] == $_SESSION['user_id'] && $membro['id_usuario'] != $_SESSION['user_id']): ?>
                        <form action="<?php echo BASE_URL; ?>group/remove_member" method="POST" 
                              onsubmit="return confirm('Tem certeza que deseja remover este membro?');">
                            <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">
                            <input type="hidden" name="id_membro" value="<?php echo $membro['id_usuario']; ?>">
                            <button type="submit" class="btn-delete" title="Remover membro">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card-footer">
            <a href="group/view/<?php echo $grupo['id_grupo']; ?>" class="btn-outline" style="text-decoration: none; display: inline-block; text-align:center;">Cancelar</a>
            
            <?php if($grupo['id_admin'] == $_SESSION['user_id']): ?>
                <button type="button" onclick="document.getElementById('formUpdateGroup').submit()" class="btn-action">Salvar</button>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
    const BASE_URL = "<?php echo BASE_URL; ?>";

    document.addEventListener("DOMContentLoaded", () => {
        const btnEdit = document.getElementById('btnEnableEdit');
        const inputName = document.getElementById('groupNameInput');

        if(btnEdit && inputName) {
            btnEdit.addEventListener('click', () => {
                inputName.readOnly = false;
                inputName.focus();
                inputName.style.borderColor = 'var(--color-primary)';
                btnEdit.style.display = 'none'; 
            });
        }
    });

    async function generateNewCode(groupId) {
        const inputDisplay = document.getElementById('inviteCodeDisplay');
        const originalValue = inputDisplay.value;
        
        inputDisplay.value = "Gerando...";
        
        try {
            const response = await fetch(BASE_URL + 'group/generateInviteCode/' + groupId, {
                method: 'POST'
            });
            const data = await response.json();

            if (data.success) {
                inputDisplay.value = data.code;
            } else {
                alert('Erro: ' + (data.error || 'Falha desconhecida'));
                inputDisplay.value = originalValue;
            }
        } catch (error) {
            console.error(error);
            alert('Erro de conexão.');
            inputDisplay.value = originalValue;
        }
    }
</script>
<?php require_once '../views/components/footer.php'; ?>