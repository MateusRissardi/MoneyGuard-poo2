<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../app/model/Expense.php';
require_once __DIR__ . '/../../app/model/Group.php';

class MockExpenseController
{
    private $expenseModel;
    private $groupModel;

    public $redirectionUrl = null;

    public function __construct($expenseModel, $groupModel)
    {
        $this->expenseModel = $expenseModel;
        $this->groupModel = $groupModel;
    }

    public function simularCreate(array $postData)
    {
        $id_grupo = $postData['id_grupo'] ?? 0;
        $id_pagador = $postData['id_pagador'] ?? null;

        $valor_total_str = preg_replace("/[^0-9,]/", "", $postData['valor_total'] ?? '0');
        $valor_total = (float) str_replace(',', '.', $valor_total_str);

        if (empty($id_pagador) || $valor_total <= 0 || empty($postData['categoria']) || empty($postData['data_despesa']) || empty($postData['descricao'])) {
            $this->redirectionUrl = "group/view/" . $id_grupo . "?error=validation";
            return;
        }

        if (strlen($postData['categoria']) > 50) {
            $this->redirectionUrl = "group/view/" . $id_grupo . "?error=" . urlencode("Categoria deve ter no máximo 50 caracteres. (RN-ORG04)");
            return;
        }

        $this->redirectionUrl = "group/view/" . $id_grupo . "?status=expense_added";
    }
}


class Cenario6ValidacaoPaganteTest extends TestCase
{
    private $validPostData = [
        'id_grupo' => 1,
        'id_pagador' => 1,
        'valor_total' => '50,00',
        'categoria' => 'Aluguel',
        'data_despesa' => '2025-10-17',
        'descricao' => 'Teste Válido',
        'tipo_divisao' => 'equitativa',
    ];

    public function test_CT_ORG_U06_1_RejeitaPagadorNuloOuVazio()
    {
        $invalidData = $this->validPostData;
        $invalidData['id_pagador'] = null;

        $expenseModelMock = $this->createMock(Expense::class);
        $groupModelMock = $this->createMock(Group::class);

        $controller = new MockExpenseController($expenseModelMock, $groupModelMock);
        $controller->simularCreate($invalidData);

        $expectedUrl = "group/view/{$this->validPostData['id_grupo']}?error=validation";
        $this->assertEquals($expectedUrl, $controller->redirectionUrl);

        $expenseModelMock->expects($this->never())->method('create');
    }
}