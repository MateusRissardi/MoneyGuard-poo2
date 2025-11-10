<?php
require_once '../views/components/header.php';

?>

<nav>
    <a href="../dashboard">&laquo; Voltar para Meus Grupos</a>

    <span style="margin: 0 10px;">|</span>
    <a href="../group/report/<?php echo $grupo['id_grupo']; ?>">Ver Relatórios (CDU08)</a>
</nav>

<h2><?php echo htmlspecialchars($grupo['nome_grupo']); ?></h2>

<div style="background: #f0f0f0; padding: 15px; margin-bottom: 20px;">
    <h3>Balanço do Grupo (CDU06)</h3>

    <ul style="list-style-type: none; padding: 0;">
        <?php
        $soma_total_zero = 0;

        if (isset($saldos)):
            foreach ($saldos as $saldo):
                $valor_saldo = $saldo['total_credito'] - $saldo['total_debito'];
                $soma_total_zero += $valor_saldo;
                ?>
                <li>
                    <strong><?php echo htmlspecialchars($saldo['nome']); ?>:</strong>

                    <?php if ($valor_saldo > 0.01): ?>
                        <span style="color: green; font-weight: bold;">
                            Recebe R$ <?php echo number_format($valor_saldo, 2, ',', '.'); ?>
                        </span>
                    <?php elseif ($valor_saldo < -0.01): ?>
                        <span style="color: red; font-weight: bold;">
                            Deve R$ <?php echo number_format(abs($valor_saldo), 2, ',', '.'); ?>
                        </span>
                    <?php else: ?>
                        <span>
                            Está quite (R$ 0,00)
                        </span>
                    <?php endif; ?>
                </li>
                <?php
            endforeach;
        endif;
        ?>
    </ul>

    <p style="font-size: 0.8em; color: #555;">
        Balanço total do grupo: R$ <?php echo number_format($soma_total_zero, 2, ',', '.'); ?>
    </p>

    <a href="?simplify=1"
        style="display: inline-block; padding: 10px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px;">
        Simplificar Dívidas (HU017)
    </a>
    <a href="../group/view/<?php echo $grupo['id_grupo']; ?>" style="margin-left: 10px;">
        (Limpar)
    </a>

    <?php if (isset($transacoes_simplificadas)): ?>

        <?php if (empty($transacoes_simplificadas)): ?>

            <?php if (isset($_GET['simplify'])): ?>
                <p style="color: green; margin-top: 15px;"><b>Parabéns, o grupo está zerado!</b> Ninguém deve a ninguém.</p>
            <?php endif; ?>

        <?php else: ?>
            <div style="margin-top: 20px; border-top: 1px solid #ccc; padding-top: 15px;">
                <h4>Pagamentos Sugeridos (Caminho mais curto):</h4>
                <ul style="list-style-type: '💸'; padding-left: 20px;">
                    <?php foreach ($transacoes_simplificadas as $transacao): ?>
                        <li>
                            <b><?php echo htmlspecialchars($transacao['devedor']); ?></b>
                            deve pagar
                            <b>R$ <?php echo number_format($transacao['valor'], 2, ',', '.'); ?></b>
                            para
                            <b><?php echo htmlspecialchars($transacao['credor']); ?></b>.
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php if ($grupo['id_admin'] == $_SESSION['user_id']): ?>
    <div style="background: #f0e0e0; padding: 15px; margin-bottom: 20px;">
        <h3>Painel do Administrador</h3>

        <p>
            <strong>Código de Convite (CDU11):</strong>

            <?php if (empty($grupo['codigo_convite'])): ?>
                <span>Nenhum código gerado.</span>
            <?php else: ?>
                <b style="font-family: monospace;"><?php echo htmlspecialchars($grupo['codigo_convite']); ?></b>
                <?php
                if (!empty($grupo['codigo_data_geracao'])) {
                    $data_geracao = new DateTime($grupo['codigo_data_geracao']);
                    $data_expiracao = $data_geracao->add(new DateInterval('P5D')); // Adiciona 5 dias
                    $agora = new DateTime();
                    if ($agora > $data_expiracao) {
                        echo '<span style="color: red;">(Expirado - RN-ORG13)</span>';
                    } else {
                        echo '(Expira em: ' . $data_expiracao->format('d/m/Y') . ')';
                    }
                }
                ?>
            <?php endif; ?>
        </p>

        <form action="../../group/generate_code/<?php echo $grupo['id_grupo']; ?>" method="POST" style="display: inline;">
            <button type="submit">Gerar Novo Código (HU008)</button>
        </form>

        <hr style="margin: 15px 0;">

        <form action="../../group/update" method="POST" style="display: inline-block; margin-right: 10px;">
            <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">
            <label for="nome_grupo_edit">Editar Nome:</label>
            <input type="text" id="nome_grupo_edit" name="nome_grupo"
                value="<?php echo htmlspecialchars($grupo['nome_grupo']); ?>" required>
            <button type="submit">Atualizar (HU004)</button>
        </form>

        <form action="../../group/delete" method="POST" style="display: inline-block;"
            onsubmit="return confirm('Tem certeza que deseja excluir este grupo? (MSG23)');">
            <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">
            <button type="submit"
                style="background-color: #dc3545; color: white; border: none; padding: 5px 10px; cursor: pointer;">
                Excluir Grupo (HU005)
            </button>
        </form>
    </div>
<?php endif; ?>

<div style="background: #e0f0e0; padding: 15px; margin-bottom: 20px;">
    <h3>Adicionar Nova Despesa (CDU04)</h3>

    <?php if (isset($_GET['error'])): ?>
        <div style="color: red;"><?php echo htmlspecialchars(urldecode($_GET['error'])); ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'expense_added'): ?>
        <div style="color: green;">Despesa registrada com sucesso! (MSG01)</div>
    <?php endif; ?>

    <form action="../../expense/create" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">

        <div>
            <label for="valor_total">Valor Total:</label>
            <input type="number" step="0.01" id="valor_total" name="valor_total" required>
        </div>
        <div>
            <label for="categoria">Categoria:</label>
            <input type="text" id="categoria" name="categoria" required>
        </div>
        <div>
            <label for="data_despesa">Data:</label>
            <input type="date" id="data_despesa" name="data_despesa" required>
        </div>
        <div>
            <label for="recibo">Anexar Recibo (Opcional):</label>
            <input type="file" id="recibo" name="recibo">
        </div>

        <hr style="margin: 15px 0;">

        <div>
            <strong>Tipo de Divisão:</strong>
            <label>
                <input type="radio" name="tipo_divisao" value="equitativa" checked
                    onclick="toggleDivisao('equitativa')">
                Equitativa
            </label>
            <label style="margin-left: 10px;">
                <input type="radio" name="tipo_divisao" value="manual" onclick="toggleDivisao('manual')">
                Manual
            </label>
        </div>

        <div id="div_manual_inputs" style="display: none; background: #d0e0d0; padding: 10px; margin-top: 10px;">
            <p><strong>Divisão Manual (RF-ORG03)</strong><br>
                (A soma deve ser igual ao Valor Total. Deixe 0 se o membro não participou.)</p>

            <?php foreach ($membros as $membro): ?>
                <div>
                    <label for="div_manual_<?php echo $membro['id_usuario']; ?>" style="width: 150px;">
                        <?php echo htmlspecialchars($membro['nome']); ?>:
                    </label>
                    R$ <input type="text" name="divisao_manual[<?php echo $membro['id_usuario']; ?>]"
                        id="div_manual_<?php echo $membro['id_usuario']; ?>" value="0,00" placeholder="0,00">
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" style="margin-top: 15px;">Adicionar Despesa (HU010)</button>
    </form>
</div>

<div style="background: #e0e0f0; padding: 15px; margin-bottom: 20px;">
    <h3>Registrar Acerto de Contas (CDU05)</h3>
    <p>Eu paguei para...</p>

    <form action="../../settlement/create" method="POST">
        <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">

        <div>
            <label for="id_credor">Quem recebeu?</label>
            <select id="id_credor" name="id_credor" required>
                <option value="">-- Selecione um membro --</option>
                <?php
                // Popula o dropdown com membros, exceto você mesmo
                $user_id_logado = $_SESSION['user_id'];
                foreach ($membros as $membro):
                    if ($membro['id_usuario'] != $user_id_logado):
                        ?>
                        <option value="<?php echo $membro['id_usuario']; ?>">
                            <?php echo htmlspecialchars($membro['nome']); ?>
                        </option>
                        <?php
                    endif;
                endforeach;
                ?>
            </select>
        </div>
        <div>
            <label for="valor">Valor:</label>
            <input type="number" step="0.01" id="valor" name="valor" required>
        </div>
        <div>
            <label for="data_pagamento">Data do Pagamento:</label>
            <input type="date" id="data_pagamento" name="data_pagamento" required>
        </div>

        <button type="submit">Registrar Acerto (HU014)</button>
    </form>
</div>

<div style="display: flex; gap: 20px;">

    <div style="flex: 3;">
        <h3>Histórico de Despesas (CDU07)</h3>

        <form method="GET" action="" style="background: #eee; padding: 10px; margin-bottom: 10px;">
            <input type="hidden" name="url" value="group/view/<?php echo $grupo['id_grupo']; ?>">

            <label for="filtro_pagador">Pagador:</label>
            <select name="filtro_pagador" id="filtro_pagador">
                <option value="">-- Todos --</option>
                <?php foreach ($membros as $membro): ?>
                    <option value="<?php echo $membro['id_usuario']; ?>" <?php
                       if (isset($filtros['id_pagador']) && $filtros['id_pagador'] == $membro['id_usuario'])
                           echo 'selected';
                       ?>>
                        <?php echo htmlspecialchars($membro['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="filtro_categoria" style="margin-left: 10px;">Categoria:</label>
            <input type="text" name="filtro_categoria" id="filtro_categoria"
                value="<?php echo htmlspecialchars($filtros['categoria'] ?? ''); ?>">

            <button type="submit">Filtrar (HU019)</button>
            <a href="../group/view/<?php echo $grupo['id_grupo']; ?>">(Limpar Filtros)</a>
        </form>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Categoria</th>
                    <th>Pagador</th>
                    <th>Valor Total</th>
                    <th>Recibo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($despesas)): ?>
                    <tr>
                        <td colspan="6">Nenhuma despesa registrada.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($despesas as $despesa): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($despesa['data_despesa']); ?></td>
                            <td><?php echo htmlspecialchars($despesa['categoria']); ?></td>
                            <td><?php echo htmlspecialchars($despesa['nome_pagador']); ?></td>
                            <td>R$ <?php echo number_format($despesa['valor_total'], 2, ',', '.'); ?></td>

                            <td>
                                <?php if (!empty($despesa['url_recibo'])): ?>
                                    <a href="../<?php echo htmlspecialchars($despesa['url_recibo']); ?>" target="_blank">
                                        Ver
                                    </a>
                                <?php else: ?>
                                    <span>--</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php
                                if ($despesa['id_pagador'] == $_SESSION['user_id']): ?>

                                    <a href="../../expense/edit/<?php echo $despesa['id_despesa']; ?>" style="margin-right: 10px;">
                                        Editar
                                    </a>

                                    <form action="../../expense/delete" method="POST"
                                        onsubmit="return confirm('Tem certeza que deseja excluir esta despesa? (MSG22)');"
                                        style="display: inline;">

                                        <input type="hidden" name="id_despesa" value="<?php echo $despesa['id_despesa']; ?>">
                                        <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">

                                        <button type.php="submit"
                                            style="color: red; background: none; border: none; cursor: pointer; padding: 0;">
                                            Excluir
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div style="flex: 1; background: #f0f0f0; padding: 15px;">
        <h3>Membros (CDU10)</h3>
        <ul>
            <?php
            $user_id_logado = $_SESSION['user_id'];
            $is_admin = ($grupo['id_admin'] == $user_id_logado);

            foreach ($membros as $membro):
                ?>
                <li>
                    <?php echo htmlspecialchars($membro['nome']); ?>
                    <?php if ($membro['id_usuario'] == $grupo['id_admin'])
                        echo '<b>(Admin)</b>'; ?>

                    <?php
                    // O Admin pode remover outros, mas não ele mesmo
                    if ($is_admin && $membro['id_usuario'] != $user_id_logado):
                        ?>
                        <form action="../../group/remove_member" method="POST" style="display: inline; margin-left: 10px;"
                            onsubmit="return confirm('Tem certeza que deseja remover este participante? (MSG32)');">
                            <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">
                            <input type="hidden" name="id_membro" value="<?php echo $membro['id_usuario']; ?>">
                            <button type="submit"
                                style="color: red; background: none; border: none; cursor: pointer; padding: 0;">
                                (Remover)
                            </button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php if ($grupo['id_admin'] == $_SESSION['user_id']): ?>
            <hr>
            <h4>Adicionar Membro (HU006)</h4>
            <form action="../../group/add_member" method="POST">
                <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">
                <div>
                    <label for="email">E-mail do Membro:</label>
                    <input type="email" name="email" id="email" placeholder="email@exemplo.com" required>
                </div>
                <button type="submit">Adicionar</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div style="margin-top: 30px;">
    <h3>Histórico de Acertos de Contas (CDU07)</h3>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Devedor (Pagou)</th>
                <th>Credor (Recebeu)</th>
                <th>Valor</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($acertos)): ?>
                <tr>
                    <td colspan="5">Nenhum acerto de contas registrado.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($acertos as $acerto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($acerto['data_pagamento']); ?></td>
                        <td><?php echo htmlspecialchars($acerto['nome_devedor']); ?></td>
                        <td><?php echo htmlspecialchars($acerto['nome_credor']); ?></td>
                        <td>R$ <?php echo number_format($acerto['valor'], 2, ',', '.'); ?></td>

                        <td>
                            <?php
                            if ($acerto['id_devedor'] == $_SESSION['user_id'] || $grupo['id_admin'] == $_SESSION['user_id']):
                                ?>
                                <form action="../../settlement/delete" method="POST"
                                    onsubmit="return confirm('Tem certeza que deseja excluir este acerto? (MSG22)');">

                                    <input type="hidden" name="id_acerto" value="<?php echo $acerto['id_acerto']; ?>">
                                    <input type="hidden" name="id_grupo" value="<?php echo $grupo['id_grupo']; ?>">

                                    <button type="submit"
                                        style="color: red; background: none; border: none; cursor: pointer; padding: 0;">
                                        Excluir
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../views/components/footer.php';
?>

<script>
    function toggleDivisao(tipo) {
        if (tipo === 'manual') {
            document.getElementById('div_manual_inputs').style.display = 'block';
        } else {
            document.getElementById('div_manual_inputs').style.display = 'none';
        }
    }
</script>