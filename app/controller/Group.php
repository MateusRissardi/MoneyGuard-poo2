<?php

require_once '../app/model/Group.php';
require_once '../app/model/User.php';
require_once '../app/model/Expense.php';

class GroupController
{
    private $db;
    private $user_id;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();

        if (!isset($_SESSION['user_id'])) {
            header("Location: login?error=auth");
            exit;
        }
        $this->user_id = $_SESSION['user_id'];
    }

    public function index()
    {
        $groupModel = new Group($this->db);
        $grupos = $groupModel->getGroupsByUser($this->user_id);

        if (empty($grupos)) {
            require_once '../views/pages/dashboard.php';
        } else {
            $target_grupo_id = null;

            if (isset($_SESSION['ultimo_grupo_acessado_id'])) {
                $ultimo_id = $_SESSION['ultimo_grupo_acessado_id'];

                if ($groupModel->isUserMember($ultimo_id, $this->user_id)) {
                    $target_grupo_id = $ultimo_id;
                }
            }

            if ($target_grupo_id === null) {
                $target_grupo_id = $grupos[0]['id_grupo'];
            }

            header("Location: group/view/" . $target_grupo_id);
            exit;
        }
    }

    // Método para CDU09 (Criar Grupo)
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nome_grupo = $_POST['nome_grupo'];

            if (empty($nome_grupo)) {
                header("Location: ../dashboard?error=empty_group_name");
                exit;
            }

            $groupModel = new Group($this->db);
            $result = $groupModel->create($nome_grupo, $this->user_id);

            if (is_int($result)) {
                header("Location: ../group/view/" . $result . "?status=group_created");
                exit;
            } else {
                header("Location: ../dashboard?error=db_fail");
                exit;
            }
        } else {
            header("Location: ../dashboard");
            exit;
        }
    }

    public function view($id_grupo)
    {
        $groupModel = new Group($this->db);

        if (!$groupModel->isUserMember($id_grupo, $this->user_id)) {
            header("Location: ../../dashboard?error=not_member");
            exit;
        }

        $userModel = new User($this->db);
        $userModel->setLastAccessedGroup($this->user_id, $id_grupo);
        $_SESSION['ultimo_grupo_acessado_id'] = $id_grupo;

        $filtros = [
            'categoria' => $_GET['filtro_categoria'] ?? null,
            'id_pagador' => $_GET['filtro_pagador'] ?? null
        ];

        $filtros = array_filter($filtros);

        $grupo = $groupModel->getGroupById($id_grupo);
        $membros = $groupModel->getMembersByGroup($id_grupo); // (CDU10)
        $despesas = $groupModel->getExpensesByGroup($id_grupo, $this->user_id, $filtros);// (CDU07)
        $acertos = $groupModel->getSettlementsByGroup($id_grupo);

        require_once '../app/model/Expense.php';
        $expenseModel = new Expense($this->db);
        $saldos = $expenseModel->getBalance($id_grupo);

        $transacoes_simplificadas = [];
        if (isset($_GET['simplify']) && $_GET['simplify'] == '1') {
            $transacoes_simplificadas = $expenseModel->simplifyDebts($id_grupo);
        }
        require_once '../views/pages/group_view.php';
    }

    public function addMember()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: ../dashboard");
            exit;
        }

        $id_grupo = $_POST['id_grupo'];
        $email = $_POST['email'];

        $groupModel = new Group($this->db);
        $grupo = $groupModel->getGroupById($id_grupo);

        header_remove("Pragma");
        header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");

        if ($grupo['id_admin'] != $this->user_id) {
            header("Location: ../group/view/$id_grupo?error=not_admin");
            exit;
        }

        if (empty($email)) {
            header("Location: ../group/view/$id_grupo?error=member_email_empty");
            exit;
        }

        $userModel = new User($this->db);
        $result = $groupModel->addMemberByEmail($id_grupo, $email, $userModel);

        if ($result === true) {
            header("Location: ../group/view/$id_grupo?status=member_added");
        } else {
            header("Location: ../group/view/$id_grupo?error=" . urlencode($result));
        }
        exit;
    }

    public function joinWithCode()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: ../dashboard");
            exit;
        }

        $codigo_convite = $_POST['codigo_convite'];
        $id_usuario_logado = $this->user_id;

        if (empty($codigo_convite)) {
            header("Location: ../dashboard?error=" . urlencode("O código de convite é obrigatório."));
            exit;
        }

        $groupModel = new Group($this->db);
        $result = $groupModel->joinWithCode($codigo_convite, $id_usuario_logado);

        if (is_int($result)) {
            header("Location: ../group/view/" . $result . "?status=group_joined");
        } else {
            header("Location: ../dashboard?error=" . urlencode($result));
        }
        exit;
    }

    public function generateInviteCode($id_grupo)
    {
        $groupModel = new Group($this->db);
        $grupo = $groupModel->getGroupById($id_grupo);

        if ($grupo['id_admin'] != $this->user_id) {
            header("Location: ../../group/view/$id_grupo?error=not_admin_code");
            exit;
        }

        $novo_codigo = $groupModel->generateInviteCode($id_grupo);

        header_remove("Pragma");
        header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");


        if ($novo_codigo) {
            header("Location: group/view/$id_grupo?status=code_generated&code=" . $novo_codigo);
        } else {
            header("Location: group/view/$id_grupo?error=code_failed");
        }
        exit;
    }

    public function report($id_grupo)
    {
        $groupModel = new Group($this->db);
        if (!$groupModel->isUserMember($id_grupo, $this->user_id)) {
            header("Location: ../../dashboard?error=not_member");
            exit;
        }

        $grupo = $groupModel->getGroupById($id_grupo);

        $expenseModel = new Expense($this->db);
        $report_categoria = $expenseModel->getReportByCategory($id_grupo);
        $report_pagador = $expenseModel->getReportByPayer($id_grupo);

        require_once '../views/pages/group_report.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: ../dashboard");
            exit;
        }

        $id_grupo = $_POST['id_grupo'];
        $novo_nome = $_POST['nome_grupo'];

        header_remove("Pragma");
        header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");

        // Validação (HU-E02)
        if (empty($novo_nome)) {
            header("Location: ../group/view/$id_grupo?error=empty_group_name");
            exit;
        }

        $groupModel = new Group($this->db);
        $result = $groupModel->updateName($id_grupo, $novo_nome, $this->user_id);

        if ($result === true) {
            header("Location: ../group/view/$id_grupo?status=group_updated");
        } else {
            header("Location: ../group/view/$id_grupo?error=" . urlencode($result));
        }
        exit;
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: ../dashboard");
            exit;
        }

        $id_grupo = $_POST['id_grupo'];
        $groupModel = new Group($this->db);
        $result = $groupModel->delete($id_grupo, $this->user_id);

        if ($result === true) {
            header("Location: ../dashboard?status=group_deleted");
        } else {
            header("Location: ../group/view/$id_grupo?error=" . urlencode($result));
        }
        exit;
    }

    public function removeMember()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: ../dashboard");
            exit;
        }

        $id_grupo = $_POST['id_grupo'];
        $id_membro_remover = $_POST['id_membro'];

        $groupModel = new Group($this->db);
        $result = $groupModel->removeMember($id_grupo, $id_membro_remover, $this->user_id);

        header_remove("Pragma");
        header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");

        if ($result === true) {
            header("Location: ../group/view/$id_grupo?status=member_removed");
        } else {
            header("Location: ../group/view/$id_grupo?error=" . urlencode($result));
        }
        exit;
    }
}
?>