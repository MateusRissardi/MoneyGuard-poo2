<?php

class Settlement
{
    private $conn;
    private $table = '"Settlement"';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($id_grupo, $id_devedor, $id_credor, $valor, $data_pagamento)
    {
        try {
            $query = "INSERT INTO " . $this->table . " (id_grupo, id_devedor, id_credor, valor, data_pagamento)
                      VALUES (:id_grupo, :id_devedor, :id_credor, :valor, :data_pag)";

            $stmt = $this->conn->prepare($query);

            $stmt->execute([
                'id_grupo' => $id_grupo,
                'id_devedor' => $id_devedor,
                'id_credor' => $id_credor,
                'valor' => $valor,
                'data_pag' => $data_pagamento
            ]);

            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete($id_acerto, $id_usuario_logado, $id_admin_grupo)
    {
        try {
            $query_check = 'SELECT id_devedor FROM "Settlement" WHERE id_acerto = :id_acerto';
            $stmt_check = $this->conn->prepare($query_check);
            $stmt_check->execute(['id_acerto' => $id_acerto]);
            $devedor = $stmt_check->fetchColumn();

            if ($devedor != $id_usuario_logado && $id_admin_grupo != $id_usuario_logado) {
                return "Você não tem permissão para excluir este acerto.";
            }

            $query_del = 'DELETE FROM "Settlement" WHERE id_acerto = :id_acerto';
            $stmt_del = $this->conn->prepare($query_del);
            $stmt_del->execute(['id_acerto' => $id_acerto]);

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            return "Erro ao excluir acerto: " . $e->getMessage();
        }
    }
}
?>