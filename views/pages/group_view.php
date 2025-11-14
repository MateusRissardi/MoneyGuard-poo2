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
?>

<!-- <h3>Painel do Grupo: <?php echo htmlspecialchars($grupo['nome_grupo']); ?></h3> -->

<?php if ($is_empty_state): ?>
    <p>Bem-vindo ao seu novo grupo! Para começar, adicione as pessoas com quem você vai dividir as contas.</p>

    <div class="cards-wrapper">
        <?php if ($grupo['id_admin'] == $_SESSION['user_id']): ?>
            <div class="card-options">
                <div class="content-icon">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
                <p>Comece convidando pessoas</h3>
                <p class="caption">Adicione membros para poder dividir as despesas com eles.</p>

                <?php if (!empty($grupo['codigo_convite'])): ?>
                    <b style="font-family: monospace;"><?php echo htmlspecialchars($grupo['codigo_convite']); ?></b>
                <?php endif; ?>

                <form action="../../group/generate_code/<?php echo $grupo['id_grupo']; ?>" method="POST"
                    style="margin-top: 5px;">
                    <button type="submit" class="btn btn-primary">Gerar código</button>
                </form>
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
                    <a href="#" class="btn-add" onclick="openModal('modal-add-settlement'); return false;"
                        style="background: #555 !important;">Registrar Acerto</a>
                <?php else: ?>
                    <a href="#" class="btn-add disabled" onclick="return false;"
                        style="background: #555 !important; opacity: 0.5; cursor: not-allowed;"
                        title="Você não está devendo nada.">Registrar Acerto</a>
                <?php endif; ?>

                <a href="#" class="btn-add" onclick="openModal('modal-add-expense'); return false;"
                    style="margin-left: 10px;">+
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

            $mes_atual = '';
            ?>

            <?php foreach ($transacoes as $transacao): ?>

                <?php
                // Divisor de Mês
                $data = new DateTime($transacao['data_ordenacao']);
                $nome_mes = $data->format('F \d\e Y'); // ex: November de 2025
                if ($nome_mes != $mes_atual) {
                    echo '<h4 style="color: var(--color-primary); padding-top: 15px; border-top: 1px solid #444; margin-top: 10px;">' . $nome_mes . '</h4>';
                    $mes_atual = $nome_mes;
                }
                ?>

                <?php if ($transacao['tipo'] == 'despesa'): ?>
                    <div class="transaction-item">
                        <div class="transaction-icon">
                            <?php echo getCategoryIcon($transacao['categoria']); ?>
                        </div>
                        <div class="transaction-details">
                            <div class="title"><?php echo htmlspecialchars($transacao['descricao']); ?></div>

                            <div class="subtitle">
                                Pago por <?php echo htmlspecialchars($transacao['nome_pagador']); ?>
                                em <?php echo date('d \d\e M', strtotime($transacao['data_despesa'])); ?>
                            </div>
                        </div>
                        <div class="transaction-amount">
                            <div class="total">R$ <?php echo number_format($transacao['valor_total'], 2, ',', '.'); ?></div>
                            <?php if (isset($transacao['valor_devido']) && $transacao['valor_devido'] > 0 && $transacao['id_pagador'] != $meu_id): ?>
                                <div class="share">Você deve R$ <?php echo number_format($transacao['valor_devido'], 2, ',', '.'); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: // $transacao['tipo'] == 'acerto' ?>
                    <div class="transaction-item">
                        <div class="transaction-icon" style="background: #2EBD85;">
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
        <?php endif; ?>
    </div>

    <div class="list-section">
        <div class="list-section-header">
            <h2>Membros do grupo</h2>
            <a href="#" class="btn-manage" onclick="openModal('modal-manage-members'); return false;"
                title="Gerenciar Membros e Grupo">
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
            <span class="modal-close" onclick="closeModal('modal-add-expense')">&times;</span>
            <h5>Nova Despesa</h5>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars(urldecode($_GET['error'])); ?></div>
            <?php endif; ?>

            <form action="expense/create" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">

                <div class="form-group">
                    <label>Quem pagou</label>
                    <select name="id_pagador" class="new-modal-select">
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
                        <input type="text" name="descricao" placeholder="Ex: Airbnb, Jantar, etc." class="" required>
                    </div>
                </div>

                <div class="form-group">

                    <label>Categoria:</label>
                    <select name="categoria" class="new-modal-select" required>
                        <option value="">-- Selecione a Categoria --</option>
                        <option value="Moradia">🏠 Moradia (Ex: Aluguel, Airbnb)</option>
                        <option value="Alimentação">🛒 Alimentação (Ex: Mercado, Pizza)</option>
                        <option value="Transporte">🚗 Transporte (Ex: Gasolina, Uber)</option>
                        <option value="Lazer">🎉 Lazer (Ex: Bar, Cinema)</option>
                        <option value="Outros">💰 Outros</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label>Valor:</label>
                    <div class="form-group input-wrapper liquid-glass">
                        <i class="fa fa-key input-icon"></i>
                        <input type="text" name="valor_total" placeholder="1.500,00" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Data</label>
                    <input type="date" name="data_despesa" value="<?php echo date('Y-m-d'); ?>" class="new-modal-input"
                        required>
                </div>

                <div class="form-group-division-header">
                    <label>Para quem</label>
                    <select name="tipo_divisao" onchange="toggleDivisao(this.value)" class="new-modal-select-simple">
                        <option value="equitativa">Divisão simples</option>
                        <option value="manual">Divisão manual</option>
                    </select>
                </div>

                <div id="div_equitativa_inputs" class="division-container">
                    <?php foreach ($membros as $membro): ?>
                        <div class="member-checkbox-item">
                            <div class="member-avatar-small"></div>
                            <span><?php echo htmlspecialchars($membro['nome']); ?></span>
                            <input type="checkbox" name="divisao_equitativa[]" value="<?php echo $membro['id_usuario']; ?>"
                                checked>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div id="div_manual_inputs" class="division-container"
                    style="display: none; background: #333; padding: 10px;">
                    <p><strong>Divisão Manual</strong> (A soma deve ser exata)</p>
                    <?php foreach ($membros as $membro): ?>
                        <div>
                            <label style="color: #fff !important;"><?php echo htmlspecialchars($membro['nome']); ?>:</label>
                            R$ <input type="text" name="divisao_manual[<?php echo $membro['id_usuario']; ?>]" value="0,00"
                                style="width: 100px; display: inline-block; color: #fff; background: #555;">
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-group">
                    <label for="recibo-upload" class="new-modal-button-fake">
                        <i class="fa fa-file-invoice"></i> Anexar comprovante
                    </label>
                    <input type="file" name="recibo" id="recibo-upload" style="display: none;">
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="margin-top: 15px;">Salvar</button>
            </form>
        </div>
    </div>


    <div id="modal-add-settlement" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('modal-add-settlement')">&times;</span>

            <h3>Registrar Acerto de Contas</h3>
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
                        <?php endif; endforeach; ?>
                </select>

                <label>Valor: </label>
                <div class="form-group d-flex gap-3 mb-3">
                    <div class="form-group input-wrapper liquid-glass">
                        <i class="fa fa-key input-icon"></i>
                        <input type="number" step="0.01" name="valor" required>
                    </div>
                </div>

                <label>Data do Pagamento:</label>
                <div class="form-group d-flex gap-3 mb-3">
                    <div class="form-group input-wrapper liquid-glass">
                        <i class="fa fa-key input-icon"></i>
                         <input type="date" name="data_pagameno" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Registrar Acerto</button>
            </form>
        </div>
    </div>


    <div id="modal-manage-members" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal('modal-manage-members')">&times;</span>

            <h4>Gerenciar Grupo e Membros</h4>
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

                    <p style="margin-bottom: 5px !important; font-weight: bold;">Código de
                        Convite:<?php echo htmlspecialchars($grupo['codigo_convite']); ?></p>
                    <form action="group/generate_code/<?php echo $grupo['id_grupo']; ?>" method="POST" style="display: inline;">
                        <button type="submit" class="btn btn-primary w-100">Gerar Novo Código</button>
                    </form>

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

    <script>
        function openModal(modalId) {
            closeModal('modal-add-expense');
            closeModal('modal-add-settlement');
            closeModal('modal-manage-members');

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
    </script>

<?php endif; ?>


<?php
require_once '../views/components/footer.php';
?>