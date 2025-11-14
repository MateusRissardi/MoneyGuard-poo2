<?php

class Expense
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($id_grupo, $id_pagador, $valor_total, $categoria, $data_despesa, $descricao, $divisao, $url_recibo = null, $tipo_divisao = 'equitativa')
    {

        $this->conn->beginTransaction();
        try {
            $query_exp = 'INSERT INTO "Expense" (id_grupo, id_pagador, valor_total, categoria, data_despesa, descricao, tipo_divisao, url_recibo)
                          VALUES (:id_grupo, :id_pagador, :valor, :cat, :data_desp, :descricao, :tipo, :url_recibo)
                          RETURNING id_despesa';

            $stmt_exp = $this->conn->prepare($query_exp);
            $stmt_exp->execute([
                'id_grupo' => $id_grupo,
                'id_pagador' => $id_pagador,
                'valor' => $valor_total,
                'cat' => $categoria,
                'data_desp' => $data_despesa,
                'descricao' => $descricao,
                'tipo' => $tipo_divisao,
                'url_recibo' => $url_recibo
            ]);

            $id_despesa = $stmt_exp->fetchColumn();

            $query_split = 'INSERT INTO "ExpenseSplit" (id_despesa, id_participante, valor_devido)
                            VALUES (:id_despesa, :id_part, :valor_dev)';
            $stmt_split = $this->conn->prepare($query_split);

            foreach ($divisao as $item) {
                $stmt_split->execute([
                    'id_despesa' => $id_despesa,
                    'id_part' => $item['id_participante'],
                    'valor_dev' => $item['valor_devido']
                ]);
            }

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    public function getBalance($id_grupo)
    {
        $query = '
            SELECT
                u.id_usuario,
                u.nome,
                
                (
                    (SELECT COALESCE(SUM(valor_total), 0) 
                     FROM "Expense" 
                     WHERE id_pagador = u.id_usuario AND id_grupo = :id_grupo_a)
                     +
                    (SELECT COALESCE(SUM(valor), 0)
                     FROM "Settlement"
                     WHERE id_devedor = u.id_usuario AND id_grupo = :id_grupo_b)
                ) AS total_pago,
                 
                (
                    (SELECT COALESCE(SUM(es.valor_devido), 0) 
                     FROM "ExpenseSplit" es
                     JOIN "Expense" e ON es.id_despesa = e.id_despesa
                     WHERE es.id_participante = u.id_usuario AND e.id_grupo = :id_grupo_c)
                     +
                    (SELECT COALESCE(SUM(valor), 0)
                     FROM "Settlement"
                     WHERE id_credor = u.id_usuario AND id_grupo = :id_grupo_d)
                ) AS total_consumido
            
            FROM "User" u
            JOIN "GroupMember" gm ON u.id_usuario = gm.id_usuario
            WHERE gm.id_grupo = :id_grupo_e
        ';

        $stmt = $this->conn->prepare($query);

        $params = [
            'id_grupo_a' => $id_grupo,
            'id_grupo_b' => $id_grupo,
            'id_grupo_c' => $id_grupo,
            'id_grupo_d' => $id_grupo,
            'id_grupo_e' => $id_grupo
        ];

        $stmt->execute($params);
        
        $saldos = $stmt->fetchAll();
        foreach ($saldos as &$saldo) {
            $saldo['total_credito'] = $saldo['total_pago'];
            $saldo['total_debito'] = $saldo['total_consumido'];
        }
        
        return $saldos;
    }

    public function getExpenseById($id_despesa)
    {
        $query = 'SELECT * FROM "Expense" WHERE id_despesa = :id_despesa';
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['id_despesa' => $id_despesa]);
        return $stmt->fetch();
    }

    public function getReportByCategory($id_grupo)
    {
        $query = 'SELECT categoria, SUM(valor_total) as total_gasto
                  FROM "Expense"
                  WHERE id_grupo = :id_grupo
                  GROUP BY categoria
                  ORDER BY total_gasto DESC';

        $stmt = $this->conn->prepare($query);
        $stmt->execute(['id_grupo' => $id_grupo]);
        return $stmt->fetchAll();
    }

    public function getReportByPayer($id_grupo)
    {
        $query = 'SELECT u.nome as nome_pagador, SUM(e.valor_total) as total_pago
                  FROM "Expense" e
                  JOIN "User" u ON e.id_pagador = u.id_usuario
                  WHERE e.id_grupo = :id_grupo
                  GROUP BY u.nome
                  ORDER BY total_pago DESC';

        $stmt = $this->conn->prepare($query);
        $stmt->execute(['id_grupo' => $id_grupo]);
        return $stmt->fetchAll();
    }

    public function update($id_despesa, $id_pagador_logado, $data, $divisao_recalculada)
    {

        $this->conn->beginTransaction();
        try {
            // 1. Verificar permissão
            $despesa_atual = $this->getExpenseById($id_despesa);
            if ($despesa_atual['id_pagador'] != $id_pagador_logado) {
                return "Você não tem permissão para editar esta despesa.";
            }

            $query_exp = 'UPDATE "Expense" SET 
                            valor_total = :valor, 
                            categoria = :cat, 
                            data_despesa = :data_desp,
                            tipo_divisao = :tipo_divisao';

            $params = [
                'valor' => $data['valor_total'],
                'cat' => $data['categoria'],
                'data_desp' => $data['data_despesa'],
                'tipo_divisao' => $data['tipo_divisao'],
                'id_despesa' => $id_despesa
            ];

            if (isset($data['url_recibo'])) {
                $query_exp .= ', url_recibo = :url_recibo';
                $params['url_recibo'] = $data['url_recibo'];
            }

            $query_exp .= ' WHERE id_despesa = :id_despesa';

            $stmt_exp = $this->conn->prepare($query_exp);
            $stmt_exp->execute($params);

            $query_del_split = 'DELETE FROM "ExpenseSplit" WHERE id_despesa = :id_despesa';
            $this->conn->prepare($query_del_split)->execute(['id_despesa' => $id_despesa]);

            $query_split = 'INSERT INTO "ExpenseSplit" (id_despesa, id_participante, valor_devido)
                            VALUES (:id_despesa, :id_part, :valor_dev)';
            $stmt_split = $this->conn->prepare($query_split);

            foreach ($divisao_recalculada as $item) {
                $stmt_split->execute([
                    'id_despesa' => $id_despesa,
                    'id_part' => $item['id_participante'],
                    'valor_dev' => $item['valor_devido']
                ]);
            }

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            return "Erro ao atualizar despesa: " . $e->getMessage();
        }
    }

    public function delete($id_despesa, $id_usuario_logado)
    {
        $this->conn->beginTransaction();
        try {
            $query_check = 'SELECT id_pagador FROM "Expense" WHERE id_despesa = :id_despesa';
            $stmt_check = $this->conn->prepare($query_check);
            $stmt_check->execute(['id_despesa' => $id_despesa]);
            $pagador = $stmt_check->fetchColumn();

            if ($pagador != $id_usuario_logado) {
                return "Você não tem permissão para excluir esta despesa.";
            }

            $query_split = 'DELETE FROM "ExpenseSplit" WHERE id_despesa = :id_despesa';
            $stmt_split = $this->conn->prepare($query_split);
            $stmt_split->execute(['id_despesa' => $id_despesa]);

            $query_exp = 'DELETE FROM "Expense" WHERE id_despesa = :id_despesa';
            $stmt_exp = $this->conn->prepare($query_exp);
            $stmt_exp->execute(['id_despesa' => $id_despesa]);

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            return "Erro ao excluir despesa: " . $e->getMessage();
        }
    }

    public function simplifyDebts($id_grupo)
    {
        $saldos_atuais = $this->getBalance($id_grupo);

        $credores = [];
        $devedores = [];
        $transacoes = [];

        foreach ($saldos_atuais as $saldo_usuario) {
            $saldo = $saldo_usuario['total_credito'] - $saldo_usuario['total_debito'];

            if ($saldo > 0.01) {
                $credores[] = ['nome' => $saldo_usuario['nome'], 'saldo' => $saldo];
            } elseif ($saldo < -0.01) {
                $devedores[] = ['nome' => $saldo_usuario['nome'], 'saldo' => abs($saldo)];
            }
        }

        usort($credores, fn($a, $b) => $b['saldo'] <=> $a['saldo']);
        usort($devedores, fn($a, $b) => $b['saldo'] <=> $a['saldo']);

        while (!empty($credores) && !empty($devedores)) {
            $credor = &$credores[0];
            $devedor = &$devedores[0];

            $valor_pagamento = min($credor['saldo'], $devedor['saldo']);

            $transacoes[] = [
                'devedor' => $devedor['nome'],
                'credor' => $credor['nome'],
                'valor' => $valor_pagamento
            ];

            $credor['saldo'] -= $valor_pagamento;
            $devedor['saldo'] -= $valor_pagamento;

            if ($credor['saldo'] < 0.01) {
                array_shift($credores);
            }
            if ($devedor['saldo'] < 0.01) {
                array_shift($devedores);
            }
        }
        return $transacoes;
    }

    public function getExpenseSplits($id_despesa)
    {
        $query = 'SELECT id_participante, valor_devido FROM "ExpenseSplit" WHERE id_despesa = :id_despesa';
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['id_despesa' => $id_despesa]);

        $splits = [];
        foreach ($stmt->fetchAll() as $row) {
            $splits[$row['id_participante']] = $row['valor_devido'];
        }
        return $splits;
    }
}
?>