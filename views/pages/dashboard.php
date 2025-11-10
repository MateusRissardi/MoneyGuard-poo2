<?php
require_once '../views/components/header.php';
?>

<h2>Meus Grupos (Dashboard)</h2>

<div style="background: #f0f0f0; padding: 15px;">
    <h3>Criar Novo Grupo (CDU09)</h3>

    <?php if (isset($error)): ?>
        <div style="color: red;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] == 'empty_group_name'): ?>
        <div style="color: red;">O nome do grupo é obrigatório. (MSG25)</div>
    <?php endif; ?>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'group_created'): ?>
        <div style="color: green;">Grupo criado com sucesso! (MSG02)</div>
    <?php endif; ?>

    <form action="group/create" method="POST">
        <label for="nome_grupo">Nome do Grupo:</label>
        <input type="text" id="nome_grupo" name="nome_grupo" required>
        <button type="submit">Criar Grupo (HU003)</button>
    </form>
</div>

<hr>

<h3>Grupos que participo:</h3>
<div class="lista-grupos">
    <?php if (isset($grupos) && !empty($grupos)): ?>
        <ul>
            <?php foreach ($grupos as $grupo): ?>
                <li>
                    <a href="group/view/<?php echo $grupo['id_grupo']; ?>">
                        <?php echo htmlspecialchars($grupo['nome_grupo']); ?>
                    </a>
                    (Admin: <?php echo ($grupo['id_admin'] == $_SESSION['user_id']) ? 'Sim' : 'Não'; ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Você ainda não participa de nenhum grupo. Crie um novo!</p>
    <?php endif; ?>
</div>


<?php
require_once '../views/components/footer.php';
?>