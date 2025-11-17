<?php
require_once '../views/components/header.php';
?>

<nav>
    <a href="../group/view/<?php echo $despesa['id_grupo']; ?>">&laquo; Voltar para o Grupo</a>
</nav>

<div style="background: #e0f0e0; padding: 15px;">

    <?php if (isset($_GET['error'])): ?>
        <div style="color: red;">
            Erro: <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
        </div>
    <?php endif; ?>

    <form action="../expense/update" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_despesa" value="<?php echo $despesa['id_despesa']; ?>">
        <input type="hidden" name="id_grupo" value="<?php echo $despesa['id_grupo']; ?>">

        <div>
            <label for="valor_total">Valor Total:</label>
            <input type="number" step="0.01" id="valor_total" name="valor_total"
                value="<?php echo htmlspecialchars($despesa['valor_total']); ?>" required>
        </div>
        <div>
            <label for="categoria">Categoria:</label>
            <input type="text" id="categoria" name="categoria"
                value="<?php echo htmlspecialchars($despesa['categoria']); ?>" required>
        </div>
        <div>
            <label for="data_despesa">Data:</label>
            <input type="date" id="data_despesa" name="data_despesa"
                value="<?php echo htmlspecialchars($despesa['data_despesa']); ?>" required>
        </div>
        <div>
            <label for="recibo">Substituir Recibo (Opcional):</label>
            <input type="file" id="recibo" name="recibo">
            <?php if (!empty($despesa['url_recibo'])): ?>
                <a href="../<?php echo htmlspecialchars($despesa['url_recibo']); ?>" target="_blank">(Ver Recibo Atual)</a>
            <?php endif; ?>
        </div>

        <hr style="margin: 15px 0;">

        <div>
            <strong>Tipo de Divisão:</strong>
            <?php $tipo_divisao = $despesa['tipo_divisao']; ?>
            <label>
                <input type="radio" name="tipo_divisao" value="equitativa" <?php if ($tipo_divisao == 'equitativa')
                    echo 'checked'; ?> onclick="toggleDivisao('equitativa')">Equitativa
            </label>
            <label style="margin-left: 10px;">
                <input type="radio" name="tipo_divisao" value="manual" <?php if ($tipo_divisao == 'manual')
                    echo 'checked'; ?> onclick="toggleDivisao('manual')"> Manual
            </label>
        </div>

        <div id="div_manual_inputs"
            style="display: <?php echo ($tipo_divisao == 'manual') ? 'block' : 'none'; ?>; background: #d0e0d0; padding: 10px; margin-top: 10px;">
            <p><strong>Divisão Manual (RF-ORG03)</strong><br>
                (A soma deve ser igual ao Valor Total. Deixe 0 se o membro não participou.)</p>

            <?php foreach ($membros as $membro):
                $valor_atual = $splits_atuais[$membro['id_usuario']] ?? 0;
                ?>
                <div>
                    <label for="div_manual_<?php echo $membro['id_usuario']; ?>" style="width: 150px;">
                        <?php echo htmlspecialchars($membro['nome']); ?>:
                    </label>
                    R$ <input type="text" name="divisao_manual[<?php echo $membro['id_usuario']; ?>]"
                        id="div_manual_<?php echo $membro['id_usuario']; ?>"
                        value="<?php echo number_format($valor_atual, 2, ',', '.'); // Pré-preenche ?>" placeholder="0,00">
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" style="margin-top: 15px;">Salvar Alterações (HU011)</button>
    </form>
</div>

<script>
    function toggleDivisao(tipo) {
        if (tipo === 'manual') {
            document.getElementById('div_manual_inputs').style.display = 'block';
        } else {
            document.getElementById('div_manual_inputs').style.display = 'none';
        }
    }
</script>

<?php
require_once '../views/components/footer.php';
?>