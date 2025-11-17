<?php
require_once '../views/components/header.php';

$num_membros = isset($membros) ? count($membros) : 0;
$num_despesas = isset($despesas) ? count($despesas) : 0;
$num_acertos = isset($acertos) ? count($acertos) : 0;
$meu_id = $_SESSION['user_id'];

$is_empty_state = ($num_membros <= 1 && $num_despesas == 0 && $num_acertos == 0);

$can_add_transaction = ($num_membros > 1);

function getCategoryIcon($categoria)
{
    switch (strtolower($categoria)) {
        case 'moradia':
            return '<i class="bi bi-collection"></i>';
        case 'alimentação':
            return '<i class="bi bi-basket"></i>';
        case 'transporte':
            return '<i class="bi bi-bus-front-fill"></i>';
        case 'lazer':
            return '<i class="bi bi-tree-fill"></i>';
        default:
            return '<i class="bi bi-coin"></i>';
    }
}

function getCategoryColorClass($categoria)
{
    switch (strtolower($categoria)) {
        case 'moradia':
            return 'icon-moradia';
        case 'alimentação':
            return 'icon-alimentacao';
        case 'transporte':
            return 'icon-transporte';
        case 'lazer':
            return 'icon-lazer';
        default:
            return 'icon-outros';
    }
}
?>

<?php if ($is_empty_state): ?>
    <p>Bem-vindo ao seu novo grupo! Para começar, adicione as pessoas com quem você vai dividir as contas.</p>

    <div class="cards-wrapper">
        <?php if ($grupo['id_admin'] == $_SESSION['user_id']): ?>
            <div class="card-options">
                <div class="content-icon">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
                <p>Comece convidando pessoas</p>
                <p class="caption">Adicione membros para poder dividir as despesas com eles.</p>

                <?php if (!empty($grupo['codigo_convite'])): ?>
                    <b id="empty-state-code"
                        style="font-family: monospace;"><?php echo htmlspecialchars($grupo['codigo_convite']); ?></b>
                <?php endif; ?>

                <button type="button" class="btn btn-primary" style="margin-top: 5px;"
                    onclick="fetchInviteCode('<?php echo $grupo['id_grupo']; ?>')">
                    Gerar código
                </button>
            </div>
        <?php endif; ?>

        <div class="card-options new-transaction">
            <div class="content-icon ">
                <i class="bi bi-cash-coin"></i>
            </div>
            <p>Registrar a primeira conta</p>
            <p class="caption">Assim que tiver membros, adicione uma despesa para começar a divisão.</p>

            <button class="btn disabled" disabled>
                Adicionar despesa
            </button>
        </div>
    </div>


<?php else: ?>
    <?php
    // --- Bloco de Cálculo para os Cards ---
    $total_gasto_real = 0;
    foreach ($despesas as $despesa) {
        $total_gasto_real += $despesa['valor_total'];
    }
    foreach ($acertos as $acerto) {
        $total_gasto_real += $acerto['valor'];
    }


    $meu_saldo_pessoal = 0;
    foreach ($saldos as $saldo) {
        if ($saldo['id_usuario'] == $meu_id) {
            $meu_saldo_pessoal = $saldo['total_credito'] - $saldo['total_debito'];
            break;
        }
    }

    // LÓGICA: Só pode fazer acerto se estiver devendo
    $pode_fazer_acerto = ($meu_saldo_pessoal < -0.01);
    ?>

    <div class="summary-cards">
        <div class="card-dark">
            <h3>Total de Transações</h3>
            <div class="value" style="color: var(--color-primary)"><?php echo count($despesas) + count($acertos); ?></div>
            <small style="color: var(--color-text-secondary)">(Despesas + Acertos Registrados)</small>
        </div>
        <div class="card-dark">
            <h3>Seu Saldo Pessoal</h3>
            <div class="value <?php echo $meu_saldo_pessoal >= 0 ? 'positive' : 'negative'; ?>">
                <?php if ($meu_saldo_pessoal > 0.01): ?>
                    R$ <?php echo number_format($meu_saldo_pessoal, 2, ',', '.'); ?>
                <?php elseif ($meu_saldo_pessoal < -0.01): ?>
                    - R$ <?php echo number_format(abs($meu_saldo_pessoal), 2, ',', '.'); ?>
                <?php else: ?>
                    R$ 0,00
                <?php endif; ?>
            </div>
            <small style="color: var(--color-text-secondary)">Quanto você deve receber (+)/Quanto você deve(-)</small>

        </div>
        <div class="card-dark">
            <h3>Total gasto no Grupo</h3>
            <div class="value" style="color: var(--color-primary)">R$
                <?php echo number_format($total_gasto_real, 2, ',', '.'); ?>
            </div>
            <small style="color: var(--color-text-secondary)">(Soma de despesas e acertos)</small>
        </div>
    </div>

    <div class="list-section">
        <div class="list-section-header">
            <h2>Transações recentes</h2>
            <div>
                <?php if ($pode_fazer_acerto): ?>
                    <a href="#" class="btn-add" onclick="openModal('modal-add-settlement'); return false;">
                        Registrar Acerto</a>
                <?php else: ?>
                    <a href="#" class="btn-add disabled" onclick="return false;"
                        style="background: #555 !important; opacity: 0.5; cursor: not-allowed;"
                        title="Você não está devendo nada.">Registrar Acerto</a>
                <?php endif; ?>

                <a href="#" class="btn-add" onclick="openCreateModal(); return false;" style="margin-left: 10px;">+
                    Adicionar despesa</a>
            </div>
        </div>

        <?php if (empty($despesas) && empty($acertos)): ?>
            <p>Nenhuma transação registrada ainda.</p>
        <?php else: ?>
            <?php
            $transacoes = [];
            foreach ($despesas as $d) {
                $d['tipo'] = 'despesa';
                $d['data_ordenacao'] = $d['data_despesa'];
                $transacoes[] = $d;
            }
            foreach ($acertos as $a) {
                $a['tipo'] = 'acerto';
                $a['data_ordenacao'] = $a['data_pagamento'];
                $transacoes[] = $a;
            }
            // Ordena o array mesclado pela data
            usort($transacoes, function ($a, $b) {
                return $b['data_ordenacao'] <=> $a['data_ordenacao'];
            });

            // LIMITA A 3 TRANSAÇÕES NO DASHBOARD
            $total_transacoes = count($transacoes);
            $transacoes_limitadas = array_slice($transacoes, 0, 3);

            $mes_atual = '';
            ?>

            <?php foreach ($transacoes_limitadas as $transacao): ?>

                <?php
                // Divisor de Mês
                $data = new DateTime($transacao['data_ordenacao']);
                $nome_mes = $data->format('F \d\e Y'); // ex: November de 2025
                if ($nome_mes != $mes_atual) {
                    echo '<h5 style="color: var(--color-primary); padding-top: 15px; margin-top: 10px;">' . $nome_mes . '</h5>';
                    $mes_atual = $nome_mes;
                }
                ?>

                <?php if ($transacao['tipo'] == 'despesa'): ?>
                    <div class="transaction-item" style="cursor: pointer;"
                        onclick="openEditModal(<?php echo $transacao['id_despesa']; ?>)">

                        <div class="transaction-icon <?php echo getCategoryColorClass($transacao['categoria']); ?>">
                            <?php echo getCategoryIcon($transacao['categoria']); ?>
                        </div>

                        <div class="transaction-details">
                            <div class="title"><?php echo htmlspecialchars($transacao['descricao']); ?></div>

                            <div class="subtitle">
                                Pago por <?php echo htmlspecialchars($transacao['nome_pagador']); ?>
                                em <?php echo date('d \d\e M', strtotime($transacao['data_despesa'])); ?>

                                <?php if (!empty($transacao['url_recibo'])): ?>
                                    - <a href="<?php echo htmlspecialchars($transacao['url_recibo']); ?>" target="_blank"
                                        style="color: var(--color-primary); font-weight: bold;">Ver Recibo</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="transaction-amount">
                            <div class="total">R$ <?php echo number_format($transacao['valor_total'], 2, ',', '.'); ?></div>
                            <?php if (isset($transacao['valor_devido']) && $transacao['valor_devido'] > 0 && $transacao['id_pagador'] != $meu_id): ?>
                                <div class="share">Você deve R$ <?php echo number_format($transacao['valor_devido'], 2, ',', '.'); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: // $transacao['tipo'] == 'acerto' 
                ?>
                    <div class="transaction-item">

                        <div class="transaction-icon icon-acerto">
                            <?php echo '💸'; ?>
                        </div>
                        <div class="transaction-details">
                            <div class="title">Acerto de Contas</div>
                            <div class="subtitle">
                                <?php echo htmlspecialchars($transacao['nome_devedor']); ?>
                                pagou para
                                <?php echo htmlspecialchars($transacao['nome_credor']); ?>
                                em <?php echo date('d \d\e M', strtotime($transacao['data_pagamento'])); ?>
                            </div>
                        </div>
                        <div class="transaction-amount">
                            <div class="total">R$ <?php echo number_format($transacao['valor'], 2, ',', '.'); ?></div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if ($total_transacoes > 3): ?>
                <div style="text-align: center; padding-top: 15px; margin-top: 10px;">
                    <a href="recent_activities" class="btn-add" style="background: #555 !important;">
                        Ver todas (<?php echo $total_transacoes; ?>)
                    </a>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>

    <div class="list-section">
        <div class="list-section-header">
            <h2>Membros do grupo</h2>
            <a href="group/settings/<?php echo $grupo['id_grupo']; ?>" class="btn-manage"
                title="Configurações do Grupo">
                <i class="fa fa-cog"></i>
            </a>
        </div>

        <?php foreach ($saldos as $membro): ?>
            <?php $saldo_membro = $membro['total_credito'] - $membro['total_debito']; ?>
            <div class="member-item">
                <div class="member-avatar"></div>
                <div class="member-details">
                    <?php echo htmlspecialchars($membro['nome']); ?>
                    <?php if ($membro['id_usuario'] == $grupo['id_admin'])
                        echo ' <span style="color:var(--color-primary); font-size: 0.8rem;">(Admin)</span>'; ?>
                </div>
                <div class="member-balance <?php echo $saldo_membro >= 0 ? 'positive' : 'negative'; ?>">
                    <?php if ($saldo_membro > 0.01): ?>
                        +R$ <?php echo number_format($saldo_membro, 2, ',', '.'); ?>
                    <?php elseif ($saldo_membro < -0.01): ?>
                        -R$ <?php echo number_format(abs($saldo_membro), 2, ',', '.'); ?>
                    <?php else: ?>
                        R$ 0,00
                    <?php endif; ?>
                </div>

                <?php if ($grupo['id_admin'] == $meu_id && $membro['id_usuario'] != $meu_id): ?>
                    <div class="member-options">
                        <form action="group/remove_member" method="POST" style="display: inline;"
                            onsubmit="return confirm('Tem certeza que deseja remover <?php echo htmlspecialchars($membro['nome']); ?>? (MSG32)');">
                            <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">
                            <input type="hidden" name="id_membro" value="<?php echo $membro['id_usuario']; ?>">
                            <button type="submit" style="background:none; border:none; color: #E84545; cursor: pointer;"
                                title="Remover Membro">
                                <i class="fa fa-times-circle"></i>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>


    <div id="modal-add-expense" class="modal-overlay">
        <div class="modal-content">
            <div class="d-flex justify-content-between">
                <h5 id="expense-modal-title">Nova Despesa</h5>
                <span class="modal-close" onclick="closeModal('modal-add-expense')">&times;</span>
            </div>
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
                            <option value="<?php echo $membro['id_usuario']; ?>" <?php if ($membro['id_usuario'] == $meu_id)
                                                                                        echo 'selected'; ?>>
                                <?php echo htmlspecialchars($membro['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label>Finalidade (Descrição):</label>
                    <div class="form-group input-wrapper liquid-glass">
                        <i class="fa fa-key input-icon"></i>
                        <input type="text" name="descricao" id="expense-descricao" placeholder="Ex: Airbnb, Jantar, etc."
                            class="" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Categoria:</label>
                    <select name="categoria" id="expense-categoria" class="new-modal-select" required>
                        <option value="">-- Selecione a Categoria --</option>
                        <option value="Moradia">🏠 Moradia (Ex: Aluguel, Airbnb)</option>
                        <option value="Alimentação">🛒 Alimentação (Ex: Mercado, Pizza)</option>
                        <option value="Transporte">🚗 Transporte (Ex: Gasolina, Uber)</option>
                        <option value="Lazer">🎉 Lazer (Ex: Bar, Cinema)</option>
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
                    <input type="date" name="data_despesa" id="expense-data-despesa" value="<?php echo date('Y-m-d'); ?>"
                        class="new-modal-input" required>
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

                <div id="div_manual_inputs">
                    <h5>Divisão Manual</h5>

                    <?php foreach ($membros as $membro): ?>
                        <div style="margin-bottom: 10px;">
                            <label>
                                <?php echo htmlspecialchars($membro['nome']); ?>:
                            </label>
                            <div class="form-group input-wrapper liquid-glass" style="padding: 8px 15px;">
                                <i class="bi bi-coin input-icon"></i>
                                <input type="text" class="expense-divisao-manual"
                                    name="divisao_manual[<?php echo $membro['id_usuario']; ?>]"
                                    id="expense-divisao-manual-<?php echo $membro['id_usuario']; ?>" value="0,00"
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


    <div id="modal-add-settlement" class="modal-overlay">
        <div class="modal-content">
            <div class="d-flex justify-content-between">
                <h3>Registrar Acerto de Contas</h3>
                <span class="modal-close" onclick="closeModal('modal-add-settlement')">&times;</span>
            </div>
            <p>Eu paguei para...</p>
            <form action="settlement/create" method="POST">
                <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">

                <label>Quem recebeu?</label>
                <select name="id_credor" class="new-modal-select" required>
                    <option value="">-- Selecione um membro --</option>
                    <?php foreach ($membros as $membro):
                        if ($membro['id_usuario'] != $meu_id): ?>
                            <option value="<?php echo $membro['id_usuario']; ?>"><?php echo htmlspecialchars($membro['nome']); ?>
                            </option>
                    <?php endif;
                    endforeach; ?>
                </select>

                <label>Valor: </label>
                <div class="form-group d-flex gap-3 mb-3">
                    <div class="form-group input-wrapper liquid-glass">
                        <i class="fa fa-key input-icon"></i>
                        <input type="text" name="valor" required oninput="formatCurrency(this)">
                    </div>
                </div>

                <label>Data do Pagamento:</label>
                <div class="form-group d-flex gap-3 mb-3">
                    <div class="form-group input-wrapper liquid-glass">
                        <i class="fa fa-key input-icon"></i>
                        <input type="date" name="data_pagamento" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Registrar Acerto</button>
            </form>
        </div>
    </div>


    <div id="modal-manage-members" class="modal-overlay">
        <div class="modal-content">
            <div class="d-flex justify-content-between">
                <h4>Gerenciar Grupo e Membros</h4>
                <span class="modal-close" onclick="closeModal('modal-manage-members')">&times;</span>
            </div>
            <div>
                <?php foreach ($membros as $membro): ?>
                    <div class="d-flex gap-3">
                        <i style="font-size: 16px; color: var(--color-primary)" class="bi bi-person-fill p-0"></i>
                        <p>
                            <?php echo htmlspecialchars($membro['nome']); ?>
                            <?php if ($membro['id_usuario'] == $grupo['id_admin'])
                                echo '<b>(Admin)</b>'; ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>

            <hr>

            <?php if ($grupo['id_admin'] == $meu_id): ?>
                <div>
                    <h4>Painel do Administrador</h4>

                    <div class="d-flex align-items-center">
                        <p style=" text-wrap: nowrap; font-weight: bold;">Código de Convite: <span
                                id="admin-invite-code"><?php echo htmlspecialchars($grupo['codigo_convite']); ?></span>
                        </p>
                        <button type="button" class="btn btn-primary w-100"
                            onclick="fetchInviteCode('<?php echo $grupo['id_grupo']; ?>')">
                            Gerar Novo Código
                        </button>
                    </div>
                    <hr style="border-color: #555; margin: 15px 0;">

                    <form action="group/update" method="POST" style="display: inline-block; margin-bottom: 0.5rem;"
                        class="w-100">
                        <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">
                        <label>Editar nome: </label>
                        <div class="form-group d-flex gap-3">
                            <div class="form-group input-wrapper liquid-glass">
                                <i class="fa fa-key input-icon"></i>
                                <input type="text" name="nome_grupo"
                                    value="<?php echo htmlspecialchars($grupo['nome_grupo']); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Atualizar</button>
                        </div>
                    </form>

                    <hr>

                    <form action="group/delete" method="POST" style="display: inline-block;" class="w-100"
                        onsubmit="return confirm('Tem certeza que deseja excluir este grupo?');">
                        <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">
                        <label>Excluir grupo: </label>
                        <button type="submit" class="btn btn-primary w-100"
                            style="background: #E84545 !important; border-color: #E84545 !important">Excluir Grupo</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div id="modal-show-code" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="d-flex justify-content-between">
            <h3 style="text-align: center;">Gerar código de acesso!</h3>
            <span class="modal-close" onclick="closeModal('modal-show-code')">&times;</span>
        </div>

        <p style="text-align: center; color: var(--color-text-secondary);">Compartilhe o código com as pessoas
            que você vai dividir!</p>

        <div class="form-group mb-3">
            <label>Código de Acesse </label>
            <div class="form-group d-flex gap-3">
                <div class="form-group input-wrapper liquid-glass">
                    <i class="fa fa-key input-icon"></i>
                    <input type="text" id="invite-code-display" style="margin-bottom: 0;" readonly>
                </div>
                <button class="btn btn-primary" onclick="copyCodeToClipboard()">Copiar</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openModal(modalId) {
        closeModal('modal-add-expense');
        closeModal('modal-add-settlement');
        closeModal('modal-manage-members');
        closeModal('modal-show-code');

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

    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            if (event.target.id !== 'noGroupsModal') {
                event.target.classList.remove('visible');
            }
        }
    }

    function toggleDivisao(tipo) {
        if (tipo === 'manual') {
            document.getElementById('div_manual_inputs').style.display = 'block';
            document.getElementById('div_equitativa_inputs').style.display = 'none';
        } else {
            document.getElementById('div_manual_inputs').style.display = 'none';
            document.getElementById('div_equitativa_inputs').style.display = 'block';
        }
    }

    function fetchInviteCode(id_grupo) {
        const displayInput = document.getElementById('invite-code-display');
        if (!displayInput) return;

        displayInput.value = "Gerando...";
        openModal('modal-show-code');

        const BASE_URL = "/MoneyGuard-poo2/public/";

        fetch(BASE_URL + `group/generate_code/${id_grupo}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayInput.value = data.code;
                    const codeDisplayEmpty = document.getElementById('empty-state-code');
                    if (codeDisplayEmpty) {
                        codeDisplayEmpty.textContent = data.code;
                    }
                    const adminCodeDisplay = document.getElementById('admin-invite-code');
                    if (adminCodeDisplay) {
                        adminCodeDisplay.textContent = data.code;
                    }
                } else {
                    displayInput.value = "Erro ao gerar";
                }
            })
            .catch(err => {
                console.error(err);
                displayInput.value = "Erro de conexão";
            });
    }

    function copyCodeToClipboard() {
        const displayInput = document.getElementById('invite-code-display');
        if (!displayInput) return;

        displayInput.select();

        if (navigator.clipboard) {
            navigator.clipboard.writeText(displayInput.value).then(() => {
                alert("Código copiado: " + displayInput.value);
            }).catch(err => {
                oldCopyCommand(displayInput);
            });
        } else {
            oldCopyCommand(displayInput);
        }
    }

    function oldCopyCommand(inputElement) {
        try {
            document.execCommand('copy');
            alert("Código copiado: " + inputElement.value);
        } catch (err) {
            alert("Falha ao copiar. Tente manualmente.");
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

                otherInput.value = remaining.toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                });
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
            let diffFmt = Math.abs(diff).toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });
            if (diff > 0) {
                infoDisplay.innerHTML = `<span style="color: var(--color-text-secondary);">Falta distribuir: ${diffFmt}</span>`;
            } else {
                infoDisplay.innerHTML = `<span style="color: var(--color-error);">Soma excede o total em: ${diffFmt}</span>`;
            }
        }
    }

    function openCreateModal() {
        const form = document.getElementById('expense-form');
        form.action = 'expense/create';

        document.getElementById('expense-modal-title').textContent = 'Nova Despesa';
        document.getElementById('expense-modal-submit-btn').textContent = 'Salvar';

        document.getElementById('expense-id-despesa').value = '';
        document.getElementById('expense-descricao').value = '';
        document.getElementById('expense-valor-total').value = '';
        document.getElementById('expense-data-despesa').value = '<?php echo date('Y-m-d'); ?>';
        document.getElementById('expense-categoria').value = '';
        document.getElementById('expense-id-pagador').value = '<?php echo $meu_id; ?>';

        const labelText = document.getElementById('recibo-label-text');
        if (labelText) labelText.textContent = 'Anexar comprovante';

        document.getElementById('expense-tipo-divisao').value = 'equitativa';
        toggleDivisao('equitativa');
        document.querySelectorAll('.expense-divisao-equitativa').forEach(chk => chk.checked = true);
        document.querySelectorAll('.expense-divisao-manual').forEach(inp => inp.value = 'R$ 0,00');

        const infoDisplay = document.getElementById('manual-split-info');
        if (infoDisplay) infoDisplay.innerHTML = 'A soma deve ser igual ao total.';

        document.getElementById('delete-expense-container').style.display = 'none';

        openModal('modal-add-expense');
    }

    function openEditModal(id_despesa) {
        const BASE_URL = "/MoneyGuard-poo2/public/";

        fetch(BASE_URL + `expense/get_details/${id_despesa}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                const {
                    despesa,
                    splits
                } = data;

                const form = document.getElementById('expense-form');
                form.action = 'expense/update';

                document.getElementById('expense-modal-title').textContent = 'Editar Despesa';
                document.getElementById('expense-modal-submit-btn').textContent = 'Atualizar';

                document.getElementById('expense-id-despesa').value = despesa.id_despesa;
                document.getElementById('expense-descricao').value = despesa.descricao;

                let valorFormatado = parseFloat(despesa.valor_total).toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                });
                document.getElementById('expense-valor-total').value = valorFormatado;

                document.getElementById('expense-data-despesa').value = despesa.data_despesa;
                document.getElementById('expense-categoria').value = despesa.categoria;
                document.getElementById('expense-id-pagador').value = despesa.id_pagador;

                const labelText = document.getElementById('recibo-label-text');
                if (labelText) {
                    labelText.textContent = despesa.url_recibo ? 'Substituir comprovante' : 'Anexar comprovante';
                }

                document.getElementById('expense-tipo-divisao').value = despesa.tipo_divisao;
                toggleDivisao(despesa.tipo_divisao);

                if (despesa.tipo_divisao === 'manual') {
                    document.querySelectorAll('.expense-divisao-manual').forEach(inp => {
                        const id_part = inp.name.match(/\[(\d+)\]/)[1];
                        let valorSplit = splits[id_part] ? parseFloat(splits[id_part]) : 0;
                        inp.value = valorSplit.toLocaleString('pt-BR', {
                            style: 'currency',
                            currency: 'BRL'
                        });
                    });
                    // Trigger validation visual
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
            const originalLabel = 'Anexar comprovante';

            fileInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    labelText.textContent = this.files[0].name;
                } else {

                }
            });
        }
    });
</script>


<?php
require_once '../views/components/footer.php';
?>