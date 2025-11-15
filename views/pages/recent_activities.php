<?php
require_once '../views/components/header.php';

// (Função helper de Ícone)
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

// 1. Combinar todas as atividades (Despesas, Acertos, Entradas)
$atividades = [];

foreach ($despesas as $d) {
    $atividades[] = ['tipo' => 'despesa', 'data_ordenacao' => $d['data_despesa'], 'dados' => $d];
}
foreach ($acertos as $a) {
    $atividades[] = ['tipo' => 'acerto', 'data_ordenacao' => $a['data_pagamento'], 'dados' => $a];
}
foreach ($entradas_membros as $e) {
    if ($e['nome'] != $_SESSION['user_name']) {
        $atividades[] = ['tipo' => 'entrada', 'data_ordenacao' => $e['data_entrada'], 'dados' => $e];
    }
}

// 2. Ordenar o array combinado pela data
usort($atividades, function ($a, $b) {
    return $b['data_ordenacao'] <=> $a['data_ordenacao'];
});

$mes_atual = '';
?>

<div class="list-section">
    <div class="list-section-header">
        <h2>Atividades Recentes</h2>

        <a href="#" class="btn-add" style="background: #555 !important;"
            onclick="openModal('modal-filter'); return false;">
            Filtrar <?php if ($filtros_ativos)
                echo '(Ativo)'; ?>
        </a>
    </div>

    <?php if (empty($atividades)): ?>
        <p>Nenhuma atividade encontrada<?php if ($filtros_ativos)
            echo ' para o filtro selecionado'; ?>.</p>
    <?php else: ?>
        <?php foreach ($atividades as $atividade): ?>

            <?php
            // Divisor de Mês
            $data = new DateTime($atividade['data_ordenacao']);
            $nome_mes = $data->format('F \d\e Y');
            if ($nome_mes != $mes_atual) {
                echo '<h4 style="color: var(--color-primary); padding-top: 15px; border-top: 1px solid #444; margin-top: 10px;">' . $nome_mes . '</h4>';
                $mes_atual = $nome_mes;
            }
            ?>

            <?php if ($atividade['tipo'] == 'despesa'): $item = $atividade['dados']; ?>
                <a href="group/view/<?php echo $grupo['id_grupo']; ?>#edit-<?php echo $item['id_despesa']; ?>"
                    title="Clique para ver ou editar esta despesa" class="transaction-item"
                    style="text-decoration: none !important; cursor: pointer;">

                    <div class="transaction-icon">
                        <?php echo getCategoryIcon($item['categoria']); ?>
                    </div>
                    <div class="transaction-details">
                        <div class="title"><?php echo htmlspecialchars($item['descricao']); ?></div>
                        <div class="subtitle">
                            Pago por <?php echo htmlspecialchars($item['nome_pagador']); ?>
                            em <?php echo date('d \d\e M', strtotime($item['data_despesa'])); ?>
                        </div>
                    </div>
                    <div class="transaction-amount">
                        <div class="total">R$ <?php echo number_format($item['valor_total'], 2, ',', '.'); ?></div>
                    </div>
            </div>

        <?php elseif ($atividade['tipo'] == 'acerto'): $item = $atividade['dados']; ?>
            <div class="transaction-item">
                <div class="transaction-icon" style="background: #2EBD85;">
                    <?php echo '💸'; ?>
                </div>
                <div class="transaction-details">
                    <div class="title">Acerto de Contas</div>
                    <div class="subtitle">
                        <?php echo htmlspecialchars($item['nome_devedor']); ?>
                        pagou para
                        <?php echo htmlspecialchars($item['nome_credor']); ?>
                        em <?php echo date('d \d\e M', strtotime($item['data_pagamento'])); ?>
                    </div>
                </div>
                <div class="transaction-amount">
                    <div class="total">R$ <?php echo number_format($item['valor'], 2, ',', '.'); ?></div>
                </div>
            </div>

        <?php elseif ($atividade['tipo'] == 'entrada'):
                $item = $atividade['dados']; ?>
            <div class="transaction-item">
                <div class="member-avatar" style="width: 45px; height: 45px; margin-right: 15px;"></div>
                <div class="transaction-details">
                    <div class="title"><?php echo htmlspecialchars($item['nome']); ?> entrou no grupo</div>
                    <div class="subtitle">
                        em <?php echo date('d \d\e M, H:i', strtotime($item['data_entrada'])); ?>
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
                <label>Categoria (Apenas Despesas):</label>
                <select name="filtro_categoria" class="new-modal-select">
                    <option value="">-- Todas as Categorias --</option>
                    <option value="Moradia" <?php if ($filtro_categoria_atual == 'Moradia')
                        echo 'selected'; ?>>🏠 Moradia
                    </option>
                    <option value="Alimentação" <?php if ($filtro_categoria_atual == 'Alimentação')
                        echo 'selected'; ?>>🛒
                        Alimentação</option>
                    <option value="Transporte" <?php if ($filtro_categoria_atual == 'Transporte')
                        echo 'selected'; ?>>🚗
                        Transporte</option>
                    <option value="Lazer" <?php if ($filtro_categoria_atual == 'Lazer')
                        echo 'selected'; ?>>🎉 Lazer
                    </option>
                    <option value="Outros" <?php if ($filtro_categoria_atual == 'Outros')
                        echo 'selected'; ?>>💰 Outros
                    </option>
                </select>
                <small style="color: #888; margin-top: 5px; display: block;">* Selecionar uma categoria irá ocultar os
                    "Acertos de Contas".</small>
            </div>

            <div class="form-group">
                <label>Quem Pagou (Aplica-se a Despesas e Acertos):</label>
                <select name="filtro_pagador" class="new-modal-select">
                    <option value="">-- Todos os Membros --</option>
                    <?php foreach ($membros as $membro): ?>
                        <option value="<?php echo $membro['id_usuario']; ?>" <?php if ($filtro_pagador_atual == $membro['id_usuario'])
                               echo 'selected'; ?>>
                            <?php echo htmlspecialchars($membro['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <a href="recent_activities" class="btn btn-primary"
                    style="background: #555 !important; flex: 1; text-align: center;">Limpar Filtro</a>
                <button type="submit" class="btn btn-primary" style="flex: 2;">Aplicar Filtro</button>
            </div>
        </form>
    </div>
</div>


<?php
require_once '../views/components/footer.php';
?>

<script>
    function openModal(modalId) {
        closeModal('modal-filter');

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
</script>