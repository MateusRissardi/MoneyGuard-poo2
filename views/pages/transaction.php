<?php
require_once '../views/components/header.php';

$meu_id = $_SESSION['user_id'];
$minhas_dividas_existem = false;
foreach ($transacoes_simplificadas as $transacao) {
    if ($transacao['devedor_id'] == $meu_id) {
        $minhas_dividas_existem = true;
        break;
    }
}
?>

<style>
    /* ... (os estilos que já lá estavam) ... */
    .transfer-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px 10px;
        border-bottom: 1px solid #333;
        transition: background-color 0.2s;
    }
    .transfer-item:last-child { border-bottom: none; }
    
    /* Adiciona cursor de clique apenas nas MINHAS dívidas */
    .transfer-item.clickable:hover {
        background-color: #2C2C2E;
        cursor: pointer;
    }

    .transfer-user {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .transfer-user .amount { 
        color: var(--color-text-secondary); 
        font-size: 0.9rem; 
    }
    .transfer-arrow {
        font-size: 1.5rem;
        color: var(--color-primary);
    }
</style>

<div class="list-section">
    <div class="list-section-header">
        <h2>Pagar dívidas (RF-ORG18)</h2>

        <?php if ($minhas_dividas_existem): ?>
            <form action="settlement/create_all_my_debts" method="POST"
                  onsubmit="return confirm('Tem a certeza que deseja pagar todas as suas dívidas de uma vez?');">
                <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">
                <button type="submit" class="btn-add">
                    Pagar todas as dívidas
                </button>
            </form>
        <?php else: ?>
             <a href="#" class="btn-add disabled" style="background: #555 !important; opacity: 0.5; cursor: not-allowed;" onclick="return false;">Pagar todas as dívidas</a>
        <?php endif; ?>
    </div>

    <?php if (empty($transacoes_simplificadas)): ?>
        <p>Ninguém deve nada a ninguém. Tudo certo!</p>
    <?php else: ?>
        <?php foreach ($transacoes_simplificadas as $transacao): ?>
            
            <?php

            $e_minha_divida = ($transacao['devedor_id'] == $meu_id);
            $classe_clicavel = $e_minha_divida ? 'clickable' : '';
            ?>

            <div class="transfer-item <?php echo $classe_clicavel; ?>" 
                 <?php if ($e_minha_divida): ?>
                    onclick="openPaySingleModal(
                        '<?php echo $grupo['id_grupo']; ?>',
                        '<?php echo $transacao['credor_id']; ?>',
                        '<?php echo htmlspecialchars($transacao['credor_nome']); ?>',
                        '<?php echo $transacao['valor']; ?>'
                    )"
                 <?php endif; ?>>
                
                <div class="transfer-user">
                    <div class="member-avatar"></div>
                    <div>
                        <div class="member-details"><?php echo htmlspecialchars($transacao['devedor_nome']); ?></div>
                        <div class="amount">R$ <?php echo number_format($transacao['valor'], 2, ',', '.'); ?></div>
                    </div>
                </div>

                <div class="transfer-arrow">
                    <i class="bi bi-arrow-right"></i>
                </div>

                <div class="transfer-user">
                    <div class="member-avatar"></div>
                    <div>
                        <div class="member-details"><?php echo htmlspecialchars($transacao['credor_nome']); ?></div>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div id="modal-pay-single-debt" class="modal-overlay">
    <div class="modal-content" style="max-width: 480px;">
        <span class="modal-close" onclick="closeModal('modal-pay-single-debt')">&times;</span>
        
        <h3>Pagar Dívida (RF-ORG13)</h3>
        <p id="pay-single-debt-text" style="text-align: center; margin: 15px 0; font-size: 1.1rem;">
            Tem a certeza que deseja pagar...
        </p>

        <form action="settlement/create" method="POST">
            <input type="hidden" id="pay-id-grupo" name="id_grupo" value="">
            <input type="hidden" id="pay-id-credor" name="id_credor" value="">
            <input type="hidden" id="pay-valor" name="valor" value="">
            
            <div class="form-group">
                <label>Data do Pagamento:</label>
                <div class="form-group input-wrapper liquid-glass">
                    <i class="fa fa-key input-icon"></i>
                    <input type="date" name="data_pagamento" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100" style="margin-top: 15px;">Confirmar Pagamento</button>
        </form>
    </div>
</div>


<?php
require_once '../views/components/footer.php';
?>

<script>

    function openModal(modalId) {
        closeModal('modal-pay-single-debt'); 
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('visible');
        } else {
            console.error("Erro: Modal não encontrado: " + modalId);
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

    function openPaySingleModal(id_grupo, credor_id, credor_nome, valor) {

        const valor_formatado = parseFloat(valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });


        document.getElementById('pay-single-debt-text').textContent = 
            `Tem a certeza que deseja pagar ${valor_formatado} para ${credor_nome}?`;


        document.getElementById('pay-id-grupo').value = id_grupo;
        document.getElementById('pay-id-credor').value = credor_id;
        document.getElementById('pay-valor').value = valor;


        openModal('modal-pay-single-debt');
    }
</script>