<?php

class User
{
    private $conn;
    private $table = '"User"';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function login($email, $senha)
    {
        try {
            $user = $this->findByEmail($email);

            if (!$user) {
                return "Credenciais de login inválidas.";
            }

            if (password_verify($senha, $user['senha_hash'])) {
                unset($user['senha_hash']);
                return $user;
            } else {
                return "Credenciais de login inválidas.";
            }

        } catch (PDOException $e) {
            return "Erro de login: " . $e->getMessage();
        }
    }

    public function register($nome, $email, $senha, $data_nascimento)
    {
        try {
            if ($this->findByEmail($email)) {
                return "E-mail já cadastrado."; // (MSG24)
            }

            $senha_hash = password_hash($senha, PASSWORD_BCRYPT);

            $query = "INSERT INTO " . $this->table . " (nome, email, senha_hash, data_nascimento)
                      VALUES (:nome, :email, :senha_hash, :data_nasc)
                      RETURNING id_usuario";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':senha_hash', $senha_hash);
            $stmt->bindParam(':data_nasc', $data_nascimento);

            $stmt->execute();

            return $stmt->fetchColumn();

        } catch (PDOException $e) {
            return "Erro ao cadastrar: " . $e->getMessage();
        }
    }

    public function findByEmail($email)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return false;
    }

    public function setLastAccessedGroup($id_usuario, $id_grupo)
    {
        try {
            $query = 'UPDATE "User" SET ultimo_grupo_acessado_id = :id_grupo 
                      WHERE id_usuario = :id_usuario';

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'id_grupo' => $id_grupo,
                'id_usuario' => $id_usuario
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao salvar último grupo: " . $e->getMessage());
            return false;
        }
    }

    public function getUserById($id_usuario)
    {
        $query = 'SELECT * FROM "User" WHERE id_usuario = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['id' => $id_usuario]);
        return $stmt->fetch();
    }

    public function update($id_usuario, $data)
    {
        $fields = [];
        $params = ['id' => $id_usuario];

        if (!empty($data['nome'])) {
            $fields[] = 'nome = :nome';
            $params['nome'] = $data['nome'];
        }
        if (!empty($data['email'])) {
            $fields[] = 'email = :email';
            $params['email'] = $data['email'];
        }
        if (!empty($data['senha'])) {
            $fields[] = 'senha_hash = :senha';
            $params['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        }

        if (empty($fields))
            return false;

        $query = 'UPDATE "User" SET ' . implode(', ', $fields) . ' WHERE id_usuario = :id';
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }

    public function delete($id_usuario)
    {
        $query = 'DELETE FROM "User" WHERE id_usuario = :id';
        $stmt = $this->conn->prepare($query);
        return $stmt->execute(['id' => $id_usuario]);
    }
}
?>