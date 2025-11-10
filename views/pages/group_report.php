<?php
require_once '../views/components/header.php';
?>

<nav>
    <a href="../group/view/<?php echo $grupo['id_grupo']; ?>">&laquo; Voltar para o Grupo</a>
</nav>

<h2>Relatórios de Gastos (CDU08)</h2>
<h3><?php echo htmlspecialchars($grupo['nome_grupo']); ?></h3>

<div style="display: flex; gap: 20px; margin-top: 20px;">

    <div style="flex: 1; background: #f0f0f0; padding: 15px;">
        <h4>Gastos por Categoria</h4>
        <?php if (empty($report_categoria)): ?>
            <p>Nenhuma despesa registrada.</p>
        <?php else: ?>
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th>Total Gasto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report_categoria as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['categoria']); ?></td>
                            <td>R$ <?php echo number_format($item['total_gasto'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div style="flex: 1; background: #f0f0f0; padding: 15px;">
        <h4>Total Pago por Membro</h4>
        <?php if (empty($report_pagador)): ?>
            <p>Nenhum pagamento registrado.</p>
        <?php else: ?>
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Membro (Pagador)</th>
                        <th>Total Pago</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report_pagador as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nome_pagador']); ?></td>
                            <td>R$ <?php echo number_format($item['total_pago'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

<?php
require_once '../views/components/footer.php';
?>