<?php

require_once '../app/model/Expense.php';
require_once '../app/model/Group.php';

class ExpenseController
{

    private $db;
    private $user_id;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();

        if (!isset($_SESSION['user_id'])) {
            header("Location: ../login?error=auth");
            exit;
        }
        $this->user_id = $_SESSION['user_id'];
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: ../dashboard");
            exit;
        }

        $id_grupo = $_POST['id_grupo'];
        $id_pagador = $_POST['id_pagador'];
        $valor_total_str = str_replace('.', '', $_POST['valor_total']);
        $valor_total = (float) str_replace(',', '.', $valor_total_str);
        
        $categoria = $_POST['categoria'];
        $descricao = $_POST['descricao'];

        $data_despesa = $_POST['data_despesa'];
        $tipo_divisao = $_POST['tipo_divisao'] ?? 'equitativa';

        $url_recibo = $this->handleFileUpload();
        
        if (empty($id_pagador) || empty($valor_total) || empty($categoria) || empty($data_despesa) || $valor_total <= 0 || empty($descricao)) {
            header("Location: ../group/view/$id_grupo?error=validation"); 
            exit;
        }

        $groupModel = new Group($this->db);
        if (!$groupModel->isUserMember($id_grupo, $this->user_id)) {
            echo "Erro: Você não tem permissão para adicionar despesas a este grupo.";
            exit;
        }

        if (empty($id_pagador) || empty($valor_total) || empty($categoria) || empty($data_despesa) || $valor_total <= 0) {
            header("Location: ../group/view/$id_grupo?error=validation");
            exit;
        }

        if (strlen($categoria) > 50) {
            header("Location: ../group/view/$id_grupo?error=" . urlencode("Categoria deve ter no máximo 50 caracteres. (RN-ORG04)"));
            exit;
        }

        $membros = $groupModel->getMembersByGroup($id_grupo);
        $divisao = [];
        $ids_membros_selecionados = [];

        if ($tipo_divisao == 'manual') {
            $divisao_manual = $_POST['divisao_manual'] ?? [];
            $soma_manual = 0;

            foreach ($membros as $membro) {
                $id_membro = $membro['id_usuario'];
                $valor_str = $divisao_manual[$id_membro] ?? '0';
                $valor_membro = (float) str_replace(',', '.', $valor_str);

                if ($valor_membro < 0) {
                    header("Location: ../group/view/$id_grupo?error=" . urlencode("Divisão manual não pode ter valores negativos. (RN-ORG05)"));
                    exit;
                }

                if ($valor_membro > 0) {
                    $divisao[] = [
                        'id_participante' => $id_membro,
                        'valor_devido' => $valor_membro
                    ];
                    $soma_manual += $valor_membro;
                }
            }

            if (abs($soma_manual - $valor_total) > 0.01) {
                header("Location: ../group/view/$id_grupo?error=" . urlencode("Soma da divisão manual (R$ $soma_manual) não bate com o Valor Total (R$ $valor_total). (RN-ORG02)"));
                exit;
            }

        } else {
            $ids_membros_selecionados = $_POST['divisao_equitativa'] ?? [];

            if (empty($ids_membros_selecionados)) {
                header("Location: ../group/view/$id_grupo?error=" . urlencode("A despesa deve ter pelo menos um participante. (RN-ORG09)"));
                exit;
            }

            $num_membros = count($ids_membros_selecionados);
            $valor_por_membro = round($valor_total / $num_membros, 2);

            foreach ($ids_membros_selecionados as $id_membro) {
                $divisao[] = [
                    'id_participante' => $id_membro,
                    'valor_devido' => $valor_por_membro
                ];
            }
        }

        if (empty($divisao)) {
            header("Location: ../group/view/$id_grupo?error=" . urlencode("A despesa deve ter pelo menos um participante. (RN-ORG09)"));
            exit;
        }

        $expenseModel = new Expense($this->db);
        $result = $expenseModel->create(
            $id_grupo,
            $id_pagador,
            $valor_total,
            $categoria,
            $data_despesa,
            $divisao,
            $descricao,
            $url_recibo,
            $tipo_divisao
        );

        if ($result) {
            header("Location: ../group/view/$id_grupo?status=expense_added");
        } else {
            header("Location: ../group/view/$id_grupo?error=create_failed");
        }
        exit;
    }

    public function edit($id_despesa)
    {
        $expenseModel = new Expense($this->db);
        $despesa = $expenseModel->getExpenseById($id_despesa);

        if (!$despesa || $despesa['id_pagador'] != $this->user_id) {
            header("Location: ../../dashboard?error=not_allowed_edit");
            exit;
        }

        $groupModel = new Group($this->db);
        $membros = $groupModel->getMembersByGroup($despesa['id_grupo']);
        $splits_atuais = $expenseModel->getExpenseSplits($id_despesa);

        require_once '../views/pages/expense_edit.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: ../dashboard");
            exit;
        }

        $id_despesa = $_POST['id_despesa'];
        $id_grupo = $_POST['id_grupo'];
        $tipo_divisao = $_POST['tipo_divisao'] ?? 'equitativa';

        $data = [
            'valor_total' => $_POST['valor_total'],
            'categoria' => $_POST['categoria'],
            'data_despesa' => $_POST['data_despesa'],
            'tipo_divisao' => $tipo_divisao
        ];

        $url_recibo = $this->handleFileUpload();
        if (is_string($url_recibo) && str_starts_with($url_recibo, "Erro:")) {
            header("Location: ../expense/edit/$id_despesa?error=" . urlencode($url_recibo));
            exit;
        }
        if ($url_recibo !== null) {
            $data['url_recibo'] = $url_recibo;
        }

        if (empty($data['valor_total']) || empty($data['categoria']) || empty($data['data_despesa']) || $data['valor_total'] <= 0) {
            header("Location: ../expense/edit/$id_despesa?error=validation");
            exit;
        }
        if (strlen($data['categoria']) > 50) {
            header("Location: ../expense/edit/$id_despesa?error=" . urlencode("Categoria deve ter no máximo 50 caracteres. (RN-ORG04)"));
            exit;
        }

        $groupModel = new Group($this->db);
        $membros = $groupModel->getMembersByGroup($id_grupo);
        $divisao = [];

        if ($tipo_divisao == 'manual') {
            $divisao_manual = $_POST['divisao_manual'] ?? [];
            $soma_manual = 0;

            foreach ($membros as $membro) {
                $id_membro = $membro['id_usuario'];
                $valor_str = $divisao_manual[$id_membro] ?? '0';
                $valor_membro = (float) str_replace(',', '.', $valor_str);

                if ($valor_membro < 0) {
                    header("Location: ../expense/edit/$id_despesa?error=" . urlencode("Divisão manual não pode ter valores negativos. (RN-ORG05)"));
                    exit;
                }
                if ($valor_membro > 0) {
                    $divisao[] = ['id_participante' => $id_membro, 'valor_devido' => $valor_membro];
                    $soma_manual += $valor_membro;
                }
            }
            if (abs($soma_manual - $data['valor_total']) > 0.01) {
                header("Location: ../expense/edit/$id_despesa?error=" . urlencode("Soma da divisão manual (R$ $soma_manual) não bate com o Valor Total (R$ {$data['valor_total']}). (RN-ORG02)"));
                exit;
            }
            if (empty($divisao)) {
                header("Location: ../expense/edit/$id_despesa?error=" . urlencode("A despesa deve ter pelo menos um participante. (RN-ORG09)"));
                exit;
            }

        } else {
            $num_membros = count($membros);
            $valor_por_membro = round($data['valor_total'] / $num_membros, 2);
            foreach ($membros as $membro) {
                $divisao[] = ['id_participante' => $membro['id_usuario'], 'valor_devido' => $valor_por_membro];
            }
        }

        $expenseModel = new Expense($this->db);
        $result = $expenseModel->update($id_despesa, $this->user_id, $data, $divisao);

        if ($result === true) {
            header("Location: ../group/view/$id_grupo?status=expense_updated");
        } else {
            header("Location: ../expense/edit/$id_despesa?error=" . urlencode($result));
        }
        exit;
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: ../dashboard");
            exit;
        }

        $id_despesa = $_POST['id_despesa'];
        $id_grupo = $_POST['id_grupo'];
        $id_usuario_logado = $this->user_id;

        if (empty($id_despesa) || empty($id_grupo)) {
            header("Location: ../group/view/$id_grupo?error=delete_invalid");
            exit;
        }

        $expenseModel = new Expense($this->db);
        $result = $expenseModel->delete($id_despesa, $id_usuario_logado);

        if ($result === true) {
            header("Location: ../group/view/$id_grupo?status=expense_deleted"); // (MSG03)
        } else {
            header("Location: ../group/view/$id_grupo?error=" . urlencode($result));
        }
        exit;
    }
    private function handleFileUpload()
    {
        if (isset($_FILES['recibo']) && $_FILES['recibo']['error'] == UPLOAD_ERR_OK) {

            $file = $_FILES['recibo'];
            $max_size = 5 * 1024 * 1024;
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

            // Validações
            if ($file['size'] > $max_size) {
                return "Erro: Ficheiro muito grande (Max 5MB).";
            }
            if (!in_array($file['type'], $allowed_types)) {
                return "Erro: Tipo de ficheiro não permitido (apenas JPG, PNG, GIF).";
            }

            $upload_dir = '../public/uploads/recibos/';

            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $unique_filename = md5(time() . uniqid()) . '.' . $file_extension;
            $target_path = $upload_dir . $unique_filename;

            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                return 'uploads/recibos/' . $unique_filename;
            } else {
                return "Erro ao mover o ficheiro.";
            }
        }
        return null;
    }
}
?>