<?php

require_once '../app/model/Settlement.php';

class SettlementController
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
        $id_credor = $_POST['id_credor'];
        $valor = $_POST['valor'];
        $data_pagamento = $_POST['data_pagamento'];
        $id_devedor = $this->user_id;

        if (empty($id_credor) || empty($valor) || empty($data_pagamento) || $valor <= 0) {
            header("Location: ../group/view/$id_grupo?error=settlement_validation");
            exit;
        }

        if ($id_devedor == $id_credor) {
            header("Location: ../group/view/$id_grupo?error=settlement_self");
            exit;
        }

        $settlementModel = new Settlement($this->db);
        $result = $settlementModel->create($id_grupo, $id_devedor, $id_credor, $valor, $data_pagamento);

        if ($result) {
            header("Location: ../group/view/$id_grupo?status=settlement_added"); // (MSG10)
        } else {
            header("Location: ../group/view/$id_grupo?error=settlement_failed");
        }
        exit;
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: ../dashboard");
            exit;
        }

        $id_acerto = $_POST['id_acerto'];
        $id_grupo = $_POST['id_grupo'];

        if (empty($id_acerto) || empty($id_grupo)) {
            header("Location: ../group/view/$id_grupo?error=delete_invalid");
            exit;
        }

        $groupModel = new Group($this->db);
        $grupo = $groupModel->getGroupById($id_grupo);
        $id_admin_grupo = $grupo['id_admin'];

        $settlementModel = new Settlement($this->db);
        $result = $settlementModel->delete($id_acerto, $this->user_id, $id_admin_grupo);

        if ($result === true) {
            header("Location: ../group/view/$id_grupo?status=settlement_deleted"); // (MSG03 - Reutilizado)
        } else {
            header("Location: ../group/view/$id_grupo?error=" . urlencode($result));
        }
        exit;
    }
}
?>