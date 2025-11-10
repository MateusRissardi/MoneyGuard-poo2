<?php

class Group
{
    private $conn;
    private $table = '"Group"';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($nome_grupo, $id_admin)
    {
        $this->conn->beginTransaction();

        try {
            $query_group = "INSERT INTO " . $this->table . " (nome_grupo, id_admin)
                            VALUES (:nome_grupo, :id_admin)
                            RETURNING id_grupo";

            $stmt_group = $this->conn->prepare($query_group);
            $stmt_group->bindParam(':nome_grupo', $nome_grupo);
            $stmt_group->bindParam(':id_admin', $id_admin);
            $stmt_group->execute();

            $id_grupo = $stmt_group->fetchColumn();

            $query_member = 'INSERT INTO "GroupMember" (id_usuario, id_grupo)
                             VALUES (:id_usuario, :id_grupo)';

            $stmt_member = $this->conn->prepare($query_member);
            $stmt_member->bindParam(':id_usuario', $id_admin);
            $stmt_member->bindParam(':id_grupo', $id_grupo);
            $stmt_member->execute();

            $this->conn->commit();
            return $id_grupo;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            return "Erro ao criar grupo: " . $e->getMessage();
        }
    }

    public function getGroupsByUser($id_usuario)
    {
        $query = 'SELECT g.* FROM "Group" g
                  JOIN "GroupMember" gm ON g.id_grupo = gm.id_grupo
                  WHERE gm.id_usuario = :id_usuario
                  ORDER BY g.nome_grupo';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function isUserMember($id_grupo, $id_usuario)
    {
        $query = 'SELECT 1 FROM "GroupMember" 
                  WHERE id_grupo = :id_grupo AND id_usuario = :id_usuario';
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['id_grupo' => $id_grupo, 'id_usuario' => $id_usuario]);
        return $stmt->fetchColumn() ? true : false;
    }

    public function getGroupById($id_grupo)
    {
        $query = 'SELECT * FROM "Group" WHERE id_grupo = :id_grupo';
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['id_grupo' => $id_grupo]);
        return $stmt->fetch();
    }

    public function getMembersByGroup($id_grupo)
    {
        $query = 'SELECT u.id_usuario, u.nome, u.email FROM "User" u
                  JOIN "GroupMember" gm ON u.id_usuario = gm.id_usuario
                  WHERE gm.id_grupo = :id_grupo';
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['id_grupo' => $id_grupo]);
        return $stmt->fetchAll();
    }

    public function getExpensesByGroup($id_grupo, $filtros = [])
    {
        $query = 'SELECT e.*, u.nome as nome_pagador 
                  FROM "Expense" e
                  JOIN "User" u ON e.id_pagador = u.id_usuario
                  WHERE e.id_grupo = :id_grupo';

        $params = ['id_grupo' => $id_grupo];

        if (!empty($filtros['categoria'])) {
            $query .= ' AND e.categoria = :categoria';
            $params['categoria'] = $filtros['categoria'];
        }
        if (!empty($filtros['id_pagador'])) {
            $query .= ' AND e.id_pagador = :id_pagador';
            $params['id_pagador'] = $filtros['id_pagador'];
        }

        $query .= ' ORDER BY e.data_despesa DESC, e.id_despesa DESC';

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function addMemberByEmail($id_grupo, $email, $userModel)
    {
        try {
            $user = $userModel->findByEmail($email);
            if (!$user) {
                return "Usuário não encontrado. Verifique o e-mail digitado.";
            }

            $id_usuario = $user['id_usuario'];

            if ($this->isUserMember($id_grupo, $id_usuario)) {
                return "Este usuário já faz parte do grupo.";
            }

            $query_member = 'INSERT INTO "GroupMember" (id_usuario, id_grupo)
                             VALUES (:id_usuario, :id_grupo)';

            $stmt_member = $this->conn->prepare($query_member);
            $stmt_member->bindParam(':id_usuario', $id_usuario);
            $stmt_member->bindParam(':id_grupo', $id_grupo);
            $stmt_member->execute();

            return true;

        } catch (PDOException $e) {
            return "Erro ao adicionar membro: " . $e->getMessage();
        }
    }

    public function generateInviteCode($id_grupo)
    {
        try {
            $novo_codigo = "MG-" . strtoupper(substr(md5(uniqid()), 0, 6));

            $query = 'UPDATE "Group" 
                      SET codigo_convite = :codigo, codigo_data_geracao = CURRENT_TIMESTAMP
                      WHERE id_grupo = :id_grupo';

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'codigo' => $novo_codigo,
                'id_grupo' => $id_grupo
            ]);

            return $novo_codigo;

        } catch (PDOException $e) {
            return false;
        }
    }

    public function joinWithCode($codigo_convite, $id_usuario)
    {
        try {
            $query_find = 'SELECT id_grupo, codigo_data_geracao FROM "Group" WHERE codigo_convite = :codigo';
            $stmt_find = $this->conn->prepare($query_find);
            $stmt_find->execute(['codigo' => $codigo_convite]);
            $grupo = $stmt_find->fetch();

            if (!$grupo) {
                return "Este código de convite é inválido ou expirou.";
            }

            $data_geracao = new DateTime($grupo['codigo_data_geracao']);
            $data_expiracao = $data_geracao->add(new DateInterval('P5D'));
            $agora = new DateTime();

            if ($agora > $data_expiracao) {
                return "Este código de convite é inválido ou expirou.";
            }

            $id_grupo = $grupo['id_grupo'];

            if ($this->isUserMember($id_grupo, $id_usuario)) {
                return "Este usuário já faz parte do grupo.";
            }

            $query_join = 'INSERT INTO "GroupMember" (id_usuario, id_grupo)
                             VALUES (:id_usuario, :id_grupo)';
            $stmt_join = $this->conn->prepare($query_join);
            $stmt_join->execute([
                'id_usuario' => $id_usuario,
                'id_grupo' => $id_grupo
            ]);

            return true;

        } catch (PDOException $e) {
            return "Erro ao entrar no grupo: " . $e->getMessage();
        }
    }

    public function getSettlementsByGroup($id_grupo)
    {
        $query = 'SELECT 
                    s.*, 
                    u_devedor.nome as nome_devedor,
                    u_credor.nome as nome_credor
                  FROM "Settlement" s
                  JOIN "User" u_devedor ON s.id_devedor = u_devedor.id_usuario
                  JOIN "User" u_credor ON s.id_credor = u_credor.id_usuario
                  WHERE s.id_grupo = :id_grupo
                  ORDER BY s.data_pagamento DESC, s.id_acerto DESC';

        $stmt = $this->conn->prepare($query);
        $stmt->execute(['id_grupo' => $id_grupo]);
        return $stmt->fetchAll();
    }

    public function updateName($id_grupo, $novo_nome, $id_usuario_logado)
    {
        try {
            $grupo = $this->getGroupById($id_grupo);
            if ($grupo['id_admin'] != $id_usuario_logado) {
                return "Apenas o admin pode alterar o nome do grupo.";
            }

            $query = 'UPDATE "Group" SET nome_grupo = :nome WHERE id_grupo = :id_grupo';
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['nome' => $novo_nome, 'id_grupo' => $id_grupo]);

            return true;

        } catch (PDOException $e) {
            return "Erro ao atualizar o nome: " . $e->getMessage();
        }
    }

    public function delete($id_grupo, $id_usuario_logado)
    {
        try {
            $grupo = $this->getGroupById($id_grupo);
            if ($grupo['id_admin'] != $id_usuario_logado) {
                return "Apenas o admin pode excluir o grupo.";
            }

            $query = 'DELETE FROM "Group" WHERE id_grupo = :id_grupo';
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id_grupo' => $id_grupo]);

            return true;

        } catch (PDOException $e) {
            return "Erro ao excluir o grupo: " . $e->getMessage();
        }
    }

    public function removeMember($id_grupo, $id_membro_remover, $id_usuario_logado)
    {
        try {
            $grupo = $this->getGroupById($id_grupo);
            if ($grupo['id_admin'] != $id_usuario_logado) {
                return "Apenas o admin pode remover membros.";
            }

            if ($id_membro_remover == $id_usuario_logado) {
                return "O Admin não pode remover a si mesmo do grupo.";
            }

            $query = 'DELETE FROM "GroupMember" WHERE id_grupo = :id_grupo AND id_usuario = :id_membro';
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id_grupo' => $id_grupo, 'id_membro' => $id_membro_remover]);

            return true;

        } catch (PDOException $e) {
            return "Erro ao remover membro: " . $e->getMessage();
        }
    }
}
?>