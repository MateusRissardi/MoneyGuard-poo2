<?php

use PHPUnit\Framework\TestCase;

// Mocks necessários
require_once __DIR__ . '/../../app/model/Expense.php';
require_once __DIR__ . '/../../app/model/Group.php';

class MockExpenseControllerUpdate
{
    private $expenseModel;
    public $redirectionUrl = null;
    private $userId = 1;
    private $membros = [['id_usuario' => 1, 'nome' => 'P1'], ['id_usuario' => 2, 'nome' => 'P2'], ['id_usuario' => 3, 'nome' => 'P3']];

    public function __construct($expenseModel)
    {
        $this->expenseModel = $expenseModel;
    }

    public function simularUpdate(array $postData, $isManual)
    {
        $id_despesa = $postData['id_despesa'];
        $id_grupo = $postData['id_grupo'];
        $valor_total_float = $postData['valor_total_float'];
        $tipo_divisao = $postData['tipo_divisao'];

        $divisao_recalculada = [];

        if ($tipo_divisao == 'manual') {
            $divisao_manual = $postData['divisao_manual'] ?? [];
            $soma_manual = 0;

            foreach ($this->membros as $membro) {
                $id_membro = $membro['id_usuario'];
                $valor_str = $divisao_manual[$id_membro] ?? '0';
                $valor_membro = (float) str_replace(',', '.', $valor_str); // Simplificado: ignora sanitização para focar no fluxo

                if ($valor_membro < 0) {
                    $this->redirectionUrl = "group/view/{$id_grupo}?error=" . urlencode("Divisão manual não pode ter valores negativos. (RN-ORG05)");
                    return;
                }
                if ($valor_membro > 0) {
                    $divisao_recalculada[] = ['id_participante' => $id_membro, 'valor_devido' => $valor_membro];
                    $soma_manual += $valor_membro;
                }
            }

            if (abs($soma_manual - $valor_total_float) > 0.01) {
                $this->redirectionUrl = "group/view/{$id_grupo}?error=" . urlencode("Soma da divisão manual (R$ {$soma_manual}) não bate com o Valor Total (R$ {$valor_total_float}). (RN-ORG02)");
                return;
            }

        } else { // Equitativa
            $ids_selecionados = $postData['divisao_equitativa'] ?? [];
            if (empty($ids_selecionados)) {
                $this->redirectionUrl = "group/view/{$id_grupo}?error=" . urlencode("A despesa deve ter pelo menos um participante. (RN-ORG09)");
                return;
            }
            $num_membros = count($ids_selecionados);
            $valor_por_membro = round($valor_total_float / $num_membros, 2);

            foreach ($ids_selecionados as $id_membro) {
                $divisao_recalculada[] = ['id_participante' => $id_membro, 'valor_devido' => $valor_por_membro];
            }
        }

        $dataUpdate = [
            'valor_total' => $valor_total_float,
            'categoria' => $postData['categoria'],
            'descricao' => $postData['descricao'],
            'data_despesa' => $postData['data_despesa'],
            'tipo_divisao' => $tipo_divisao,
            'id_pagador' => $postData['id_pagador']
        ];

        $result = $this->expenseModel->update($id_despesa, $this->userId, $dataUpdate, $divisao_recalculada);

        if ($result === true) {
            $this->redirectionUrl = "group/view/{$id_grupo}?status=expense_updated";
        } else {
            $this->redirectionUrl = "group/view/{$id_grupo}?error=" . urlencode("Falha na atualização");
        }
    }
}

class Cenario11EdicaoDespesaTest extends TestCase
{
    private $baseData = [
        'id_despesa' => 5,
        'id_grupo' => 10,
        'id_pagador' => 1,
        'valor_total_float' => 30.00,
        'categoria' => 'Teste',
        'descricao' => 'Edicao Teste',
        'data_despesa' => '2025-10-17',
        'tipo_divisao' => 'equitativa',
    ];
    private $membrosIds = [1, 2, 3];

    public function test_CT_ORG_I04_1_AdicaoDeParticipante()
    {
        $postData = $this->baseData;
        $postData['divisao_equitativa'] = [1, 2, 3];

        $expectedSplits = [
            ['id_participante' => 1, 'valor_devido' => 10.00],
            ['id_participante' => 2, 'valor_devido' => 10.00],
            ['id_participante' => 3, 'valor_devido' => 10.00],
        ];

        $expenseModelMock = $this->createMock(Expense::class);
        $expenseModelMock->expects($this->once())
            ->method('update')
            ->with($this->baseData['id_despesa'], 1, $this->anything(), $expectedSplits)
            ->willReturn(true);

        $controller = new MockExpenseControllerUpdate($expenseModelMock);
        $controller->simularUpdate($postData, false);

        $expectedUrl = "group/view/{$postData['id_grupo']}?status=expense_updated";
        $this->assertEquals($expectedUrl, $controller->redirectionUrl);
    }

    public function test_CT_ORG_I04_2_RemocaoDeParticipante()
    {
        $postData = $this->baseData;
        $postData['divisao_equitativa'] = [1, 3];

        // Novo split esperado: 15.00
        $expectedSplits = [
            ['id_participante' => 1, 'valor_devido' => 15.00],
            ['id_participante' => 3, 'valor_devido' => 15.00],
        ];

        $expenseModelMock = $this->createMock(Expense::class);
        $expenseModelMock->expects($this->once())
            ->method('update')
            ->with($this->baseData['id_despesa'], 1, $this->anything(), $expectedSplits)
            ->willReturn(true);

        $controller = new MockExpenseControllerUpdate($expenseModelMock);
        $controller->simularUpdate($postData, false);

        $expectedUrl = "group/view/{$postData['id_grupo']}?status=expense_updated";
        $this->assertEquals($expectedUrl, $controller->redirectionUrl);
    }

    public function test_CT_ORG_I04_3_EdicaoValorTotal()
    {
        $postData = $this->baseData;
        $postData['valor_total_float'] = 50.00;
        $postData['divisao_equitativa'] = [1, 2];

        // Novo split esperado: 25.00
        $expectedSplits = [
            ['id_participante' => 1, 'valor_devido' => 25.00],
            ['id_participante' => 2, 'valor_devido' => 25.00],
        ];

        $expenseModelMock = $this->createMock(Expense::class);
        $expenseModelMock->expects($this->once())
            ->method('update')
            ->with($this->baseData['id_despesa'], 1, $this->callback(function ($data) {
                return $data['valor_total'] == 50.00;
            }), $expectedSplits)
            ->willReturn(true);

        $controller = new MockExpenseControllerUpdate($expenseModelMock);
        $controller->simularUpdate($postData, false);

        $expectedUrl = "group/view/{$postData['id_grupo']}?status=expense_updated";
        $this->assertEquals($expectedUrl, $controller->redirectionUrl);
    }

    public function test_CT_ORG_I04_4_AlteracaoParaDivisaoManual()
    {
        $postData = $this->baseData;
        $postData['valor_total_float'] = 60.00;
        $postData['tipo_divisao'] = 'manual';
        $postData['divisao_manual'] = [
            1 => '10,00', // P1
            2 => '50,00', // P2
            3 => '0,00',  // P3
        ];

        $expectedSplits = [
            ['id_participante' => 1, 'valor_devido' => 10.00],
            ['id_participante' => 2, 'valor_devido' => 50.00],
        ];

        $expenseModelMock = $this->createMock(Expense::class);
        $expenseModelMock->expects($this->once())
            ->method('update')
            ->with($this->baseData['id_despesa'], 1, $this->callback(function ($data) {
                return $data['tipo_divisao'] == 'manual';
            }), $expectedSplits)
            ->willReturn(true);

        $controller = new MockExpenseControllerUpdate($expenseModelMock);
        $controller->simularUpdate($postData, true);

        $expectedUrl = "group/view/{$postData['id_grupo']}?status=expense_updated";
        $this->assertEquals($expectedUrl, $controller->redirectionUrl);
    }
}