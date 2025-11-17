<?php
require_once '../views/components/header.php';

// --- FUNÇÕES HELPER ---
function getCategoryIcon($categoria)
{
    switch (strtolower($categoria)) {
        case 'moradia': return '<i class="bi bi-collection"></i>';
        case 'alimentação': return '<i class="bi bi-basket"></i>';
        case 'transporte': return '<i class="bi bi-bus-front-fill"></i>';
        case 'lazer': return '<i class="bi bi-tree-fill"></i>';
        default: return '<i class="bi bi-coin"></i>';
    }
}

function getCategoryColorClass($categoria)
{
    switch (strtolower($categoria)) {
        case 'moradia': return 'icon-moradia';
        case 'alimentação': return 'icon-alimentacao';
        case 'transporte': return 'icon-transporte';
        case 'lazer': return 'icon-lazer';
        default: return 'icon-outros';
    }
}
// ----------------------

$meu_id = $_SESSION['user_id'];

// 1. Combinar todas as atividades
$atividades = [];

foreach ($despesas as $d) {
    $atividades[] = [ 'tipo' => 'despesa', 'data_ordenacao' => $d['data_despesa'], 'dados' => $d ];
}
foreach ($acertos as $a) {
    $atividades[] = [ 'tipo' => 'acerto', 'data_ordenacao' => $a['data_pagamento'], 'dados' => $a ];
}
foreach ($entradas_membros as $e) {
    if ($e['nome'] != $_SESSION['user_name']) { 
        $atividades[] = [ 'tipo' => 'entrada', 'data_ordenacao' => $e['data_entrada'], 'dados' => $e ];
    }
}

// 2. Ordenar
usort($atividades, function ($a, $b) {
    return $b['data_ordenacao'] <=> $a['data_ordenacao'];
});

$mes_atual = '';
?>

<div class="list-section">
    <div class="list-section-header">
        <h2><?php echo htmlspecialchars($grupo['nome_grupo']); ?>: Atividades Recentes</h2>
        
        <a href="#" class="btn-add" onclick="openModal('modal-filter'); return false;">
           Filtrar <?php if($filtros_ativos) echo '(Ativo)'; ?>
        </a>
    </div>

    <?php if (empty($atividades)): ?>
        <p>Nenhuma atividade encontrada<?php if($filtros_ativos) echo ' para o filtro selecionado'; ?>.</p>
    <?php else: ?>
        <?php foreach ($atividades as $atividade): ?>
            
            <?php
            // Divisor de Mês
            $data = new DateTime($atividade['data_ordenacao']);
            $nome_mes = $data->format('F \d\e Y');
            if ($nome_mes != $mes_atual) {
                echo '<h4 style="color: var(--color-primary); padding-top: 20px; border-top: 1px solid #333; margin-top: 15px; margin-bottom: 10px;">' . $nome_mes . '</h4>';
                $mes_atual = $nome_mes;
            }
            ?>

            <?php if ($atividade['tipo'] == 'despesa'): $item = $atividade['dados']; ?>
                <div class="transaction-item" style="cursor: pointer;"
                     onclick="openEditModal(<?php echo $item['id_despesa']; ?>)">
                    
                    <div class="transaction-icon <?php echo getCategoryColorClass($item['categoria']); ?>">
                        <?php echo getCategoryIcon($item['categoria']); ?>
                    </div>
                    
                    <div class="transaction-details">
                        <div class="title"><?php echo htmlspecialchars($item['descricao']); ?></div>
                        <div class="subtitle">
                            Pago por <?php echo htmlspecialchars($item['nome_pagador']); ?>, 
                            <?php echo date('d \d\e M', strtotime($item['data_despesa'])); ?>

                            <?php if (!empty($item['url_recibo'])): ?>
                                - <a href="<?php echo htmlspecialchars($item['url_recibo']); ?>" target="_blank" 
                                     style="color: var(--color-primary); font-weight: bold;"
                                     onclick="event.stopPropagation();"> Ver Recibo
                                  </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="transaction-amount">
                        <div class="total">R$ <?php echo number_format($item['valor_total'], 2, ',', '.'); ?></div>
                         <?php if (isset($item['valor_devido']) && $item['valor_devido'] > 0 && $item['id_pagador'] != $_SESSION['user_id']): ?>
                            <div class="share">Você deve R$ <?php echo number_format($item['valor_devido'], 2, ',', '.'); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($atividade['tipo'] == 'acerto'): $item = $atividade['dados']; ?>
                <div class="transaction-item">
                    <div class="transaction-icon icon-acerto">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="transaction-details">
                        <div class="title">Acerto de Contas</div>
                        <div class="subtitle">
                            <?php echo htmlspecialchars($item['nome_devedor']); ?> pagou para <?php echo htmlspecialchars($item['nome_credor']); ?>,
                            <?php echo date('d \d\e M', strtotime($item['data_pagamento'])); ?>
                        </div>
                    </div>
                    <div class="transaction-amount">
                        <div class="total">R$ <?php echo number_format($item['valor'], 2, ',', '.'); ?></div>
                    </div>
                </div>

            <?php elseif ($atividade['tipo'] == 'entrada'): $item = $atividade['dados']; ?>
                 <div class="transaction-item">
                    <div class="transaction-icon" style="background-color: #333; color: #aaa; border: 1px solid #444;">
                         <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <div class="transaction-details">
                        <div class="title"><?php echo htmlspecialchars($item['nome']); ?> entrou no grupo</div>
                        <div class="subtitle">
                            <?php echo date('d \d\e M', strtotime($item['data_entrada'])); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        <?php endforeach; ?>
    <?php endif; ?>
</div>


<div id="modal-filter" class="modal-overlay">
    <div class="modal-content" style="max-width: 480px;">
        <span class="modal-close" onclick="closeModal('modal-filter')">&times;</span>
        <h3 style="text-align: center;">Filtrar Atividades</h3>
        
        <form action="recent_activities" method="GET">
            <input type="hidden" name="filtro_submit" value="1">
            
            <div class="form-group">
                <label>Quem Pagou:</label>
                <select name="filtro_pagador" class="new-modal-select">
                    <option value="">-- Todos os Membros --</option>
                    <?php foreach ($membros as $membro): ?>
                        <option value="<?php echo $membro['id_usuario']; ?>" <?php if($filtro_pagador_atual == $membro['id_usuario']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($membro['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Categoria (Apenas Despesas):</label>
                <select name="filtro_categoria" class="new-modal-select">
                    <option value="">-- Todas as Categorias --</option>
                    <option value="Moradia" <?php if($filtro_categoria_atual == 'Moradia') echo 'selected'; ?>>🏠 Moradia</option>
                    <option value="Alimentação" <?php if($filtro_categoria_atual == 'Alimentação') echo 'selected'; ?>>🛒 Alimentação</option>
                    <option value="Transporte" <?php if($filtro_categoria_atual == 'Transporte') echo 'selected'; ?>>🚗 Transporte</option>
                    <option value="Lazer" <?php if($filtro_categoria_atual == 'Lazer') echo 'selected'; ?>>🎉 Lazer</option>
                    <option value="Outros" <?php if($filtro_categoria_atual == 'Outros') echo 'selected'; ?>>💰 Outros</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <a href="recent_activities" class="btn btn-primary" style="background: #555 !important; flex: 1; text-align: center;">Limpar</a>
                <button type="submit" class="btn btn-primary" style="flex: 2;">Aplicar Filtro</button>
            </div>
        </form>
    </div>
</div>


<div id="modal-add-expense" class="modal-overlay">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('modal-add-expense')">&times;</span>
        <h5 id="expense-modal-title">Nova Despesa</h5>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars(urldecode($_GET['error'])); ?></div>
        <?php endif; ?>

        <form id="expense-form" action="expense/create" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">
            <input type="hidden" name="id_despesa" id="expense-id-despesa" value="">

            <div class="form-group">
                <label>Quem pagou</label>
                <select name="id_pagador" id="expense-id-pagador" class="new-modal-select">
                    <?php foreach ($membros as $membro): ?>
                        <option value="<?php echo $membro['id_usuario']; ?>" <?php if ($membro['id_usuario'] == $meu_id) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($membro['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group mb-3">
                <label>Finalidade (Descrição):</label>
                <div class="form-group input-wrapper liquid-glass">
                    <i class="fa fa-key input-icon"></i>
                    <input type="text" name="descricao" id="expense-descricao" placeholder="Ex: Airbnb, Jantar, etc." class="" required>
                </div>
            </div>

            <div class="form-group">
                <label>Categoria:</label>
                <select name="categoria" id="expense-categoria" class="new-modal-select" required>
                    <option value="">-- Selecione a Categoria --</option>
                    <option value="Moradia">🏠 Moradia</option>
                    <option value="Alimentação">🛒 Alimentação</option>
                    <option value="Transporte">🚗 Transporte</option>
                    <option value="Lazer">🎉 Lazer</option>
                    <option value="Outros">💰 Outros</option>
                </select>
            </div>
            
            <div class="form-group mb-3">
                <label>Valor Total:</label>
                <div class="form-group input-wrapper liquid-glass">
                    <i class="fa fa-key input-icon"></i>
                    <input type="text" name="valor_total" id="expense-valor-total" placeholder="R$ 0,00" required
                        oninput="formatCurrency(this); autoBalanceManual(null);">
                </div>
            </div>

            <div class="form-group">
                <label>Data</label>
                <input type="date" name="data_despesa" id="expense-data-despesa"
                    value="<?php echo date('Y-m-d'); ?>" class="new-modal-input" required>
            </div>

            <div class="form-group-division-header">
                <label>Para quem</label>
                <select name="tipo_divisao" id="expense-tipo-divisao" onchange="toggleDivisao(this.value)"
                    class="new-modal-select-simple">
                    <option value="equitativa">Divisão simples</option>
                    <option value="manual">Divisão manual</option>
                </select>
            </div>

            <div id="div_equitativa_inputs" class="division-container">
                <?php foreach ($membros as $membro): ?>
                    <div class="member-checkbox-item">
                        <div class="member-avatar-small"></div>
                        <span><?php echo htmlspecialchars($membro['nome']); ?></span>
                        <input type="checkbox" class="expense-divisao-equitativa" name="divisao_equitativa[]"
                            value="<?php echo $membro['id_usuario']; ?>" checked>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="div_manual_inputs" class="division-container" style="display: none; background: #333; padding: 10px;">
                <p style="margin-bottom: 15px; color: var(--color-text-secondary);">
                    <strong>Divisão Manual</strong>
                    <br><span id="manual-split-info" style="font-size: 0.8rem;">A soma deve ser igual ao total.</span>
                </p>
                <?php foreach ($membros as $membro): ?>
                    <div style="margin-bottom: 10px;">
                        <label style="color: #fff !important; display: block; margin-bottom: 5px;">
                            <?php echo htmlspecialchars($membro['nome']); ?>:
                        </label>
                        <div class="form-group input-wrapper liquid-glass" style="padding: 8px 15px;">
                            <span style="color: var(--color-primary); margin-right: 5px;">R$</span>
                            <input type="text" class="expense-divisao-manual"
                                name="divisao_manual[<?php echo $membro['id_usuario']; ?>]"
                                id="expense-divisao-manual-<?php echo $membro['id_usuario']; ?>" value="0,00"
                                style="width: 100%; color: #fff;"
                                oninput="formatCurrency(this); autoBalanceManual(this);">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="form-group">
                <label for="recibo-upload" class="new-modal-button-fake">
                    <i class="fa fa-file-invoice"></i> <span id="recibo-label-text">Anexar comprovante</span>
                </label>
                <input type="file" name="recibo" id="recibo-upload" style="display: none;">
            </div>

            <button type="submit" id="expense-modal-submit-btn" class="btn btn-primary btn-block"
                style="margin-top: 15px;">Salvar</button>
        </form>

        <div id="delete-expense-container" style="display: none; margin-top: 15px;">
            <form id="delete-expense-form" action="expense/delete" method="POST"
                onsubmit="return confirm('Tem certeza que deseja excluir esta despesa? (MSG22)');">
                <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">
                <input type="hidden" name="id_despesa" id="delete-id-despesa" value="">
                <button type="submit" class="btn btn-primary w-100"
                    style="background: #E84545 !important; border-color: #E84545 !important">Excluir Despesa</button>
            </form>
        </div>
    </div>
</div>


<?php
require_once '../views/components/footer.php';
?>

<script>
    function openModal(modalId) {
        closeModal('modal-filter');
        closeModal('modal-add-expense');

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

    function toggleDivisao(tipo) {
        const manualInputs = document.getElementById('div_manual_inputs');
        const equitativaInputs = document.getElementById('div_equitativa_inputs');
        
        if (manualInputs && equitativaInputs) {
            if (tipo === 'manual') {
                manualInputs.style.display = 'block';
                equitativaInputs.style.display = 'none';
            } else {
                manualInputs.style.display = 'none';
                equitativaInputs.style.display = 'block';
            }
        }
    }

    function formatCurrency(input) {
        let value = input.value.replace(/\D/g, '');
        if (value === "") {
            input.value = "";
            return;
        }
        let numberValue = parseInt(value, 10) / 100;
        input.value = numberValue.toLocaleString('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        });
    }

    // Auto-Balancing Manual
    function autoBalanceManual(changedInput) {
        const totalInput = document.getElementById('expense-valor-total');
        const manualInputs = document.querySelectorAll('.expense-divisao-manual');
        const infoDisplay = document.getElementById('manual-split-info');
        
        if (!totalInput || manualInputs.length === 0) return;

        let totalStr = totalInput.value.replace(/\D/g, '');
        let totalVal = totalStr === "" ? 0 : parseInt(totalStr, 10) / 100;

        if (manualInputs.length === 2 && changedInput) {
            let otherInput = null;
            manualInputs.forEach(inp => {
                if (inp !== changedInput) otherInput = inp;
            });

            if (otherInput) {
                let changedStr = changedInput.value.replace(/\D/g, '');
                let changedVal = changedStr === "" ? 0 : parseInt(changedStr, 10) / 100;

                let remaining = totalVal - changedVal;
                if (remaining < 0) remaining = 0; 
                otherInput.value = remaining.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            }
        }

        let currentSum = 0;
        manualInputs.forEach(inp => {
            let valStr = inp.value.replace(/\D/g, '');
            let val = valStr === "" ? 0 : parseInt(valStr, 10) / 100;
            currentSum += val;
        });

        totalVal = Math.round(totalVal * 100) / 100;
        currentSum = Math.round(currentSum * 100) / 100;
        let diff = totalVal - currentSum;
        
        if (Math.abs(diff) < 0.01) {
            infoDisplay.innerHTML = '<span style="color: var(--color-success);">Soma correta!</span>';
        } else {
            let diffFmt = Math.abs(diff).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            if (diff > 0) {
                infoDisplay.innerHTML = `<span style="color: var(--color-text-secondary);">Falta distribuir: ${diffFmt}</span>`;
            } else {
                infoDisplay.innerHTML = `<span style="color: var(--color-error);">Soma excede o total em: ${diffFmt}</span>`;
            }
        }
    }

    // Abre o modal para "Editar"
    function openEditModal(id_despesa) {
        const BASE_URL = "/GitHub/MoneyGuard-poo2/public/";
        
        fetch(BASE_URL + `expense/get_details/${id_despesa}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                const { despesa, splits } = data;
                
                const form = document.getElementById('expense-form');
                form.action = 'expense/update'; 

                document.getElementById('expense-modal-title').textContent = 'Editar Despesa';
                document.getElementById('expense-modal-submit-btn').textContent = 'Atualizar';

                document.getElementById('expense-id-despesa').value = despesa.id_despesa;
                document.getElementById('expense-descricao').value = despesa.descricao;
                
                let valorFormatado = parseFloat(despesa.valor_total).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                document.getElementById('expense-valor-total').value = valorFormatado;
                
                document.getElementById('expense-data-despesa').value = despesa.data_despesa;
                document.getElementById('expense-categoria').value = despesa.categoria;
                document.getElementById('expense-id-pagador').value = despesa.id_pagador;
                
                const labelText = document.getElementById('recibo-label-text');
                if(labelText) {
                    labelText.textContent = despesa.url_recibo ? 'Substituir comprovante' : 'Anexar comprovante';
                }

                document.getElementById('expense-tipo-divisao').value = despesa.tipo_divisao;
                toggleDivisao(despesa.tipo_divisao);

                if (despesa.tipo_divisao === 'manual') {
                    document.querySelectorAll('.expense-divisao-manual').forEach(inp => {
                        const id_part = inp.name.match(/\[(\d+)\]/)[1];
                        let valorSplit = splits[id_part] ? parseFloat(splits[id_part]) : 0;
                        inp.value = valorSplit.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                    });
                    autoBalanceManual(null);
                } else {
                    document.querySelectorAll('.expense-divisao-equitativa').forEach(chk => {
                        chk.checked = !!splits[chk.value];
                    });
                }

                document.getElementById('delete-id-despesa').value = despesa.id_despesa;
                document.getElementById('delete-expense-container').style.display = 'block';

                openModal('modal-add-expense');
            })
            .catch(err => {
                console.error(err);
                alert('Erro ao buscar dados da despesa.');
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('recibo-upload');
        const labelText = document.getElementById('recibo-label-text');
        if (fileInput && labelText) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    labelText.textContent = this.files[0].name;
                }
            });
        }
    });
</script>