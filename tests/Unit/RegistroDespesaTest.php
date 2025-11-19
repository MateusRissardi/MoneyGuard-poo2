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

    public function simularCreate(array $postData, int $userId)
    {
        $valor_total_str = preg_replace("/[^0-9,]/", "", $postData['valor_total'] ?? '0');
        $valor_total = (float) str_replace(',', '.', $valor_total_str);
        
        if (empty($postData['id_pagador']) || $valor_total <= 0 || empty($postData['categoria']) || empty($postData['data_despesa']) || empty($postData['descricao'])) {
            $this->redirectionUrl = "group/view/" . ($postData['id_grupo'] ?? 0) . "?error=validation";
            return;
        }

        if (strlen($postData['categoria']) > 50) {
            $this->redirectionUrl = "group/view/" . ($postData['id_grupo'] ?? 0) . "?error=" . urlencode("Categoria deve ter no máximo 50 caracteres. (RN-ORG04)");
            return;
        }
        
        if (empty($postData['divisao_equitativa']) && ($postData['tipo_divisao'] ?? 'equitativa') == 'equitativa') {
            $this->redirectionUrl = "group/view/" . ($postData['id_grupo'] ?? 0) . "?error=" . urlencode("A despesa deve ter pelo menos um participante. (RN-ORG09)");
            return;
        }
        
        $divisao = [['id_participante' => $userId, 'valor_devido' => $valor_total]];
        $result = $this->expenseModel->create(
            $postData['id_grupo'],
            $postData['id_pagador'],
            $valor_total,
            $postData['categoria'],
            $postData['data_despesa'],
            $postData['descricao'],
            $divisao,
            null,
            $postData['tipo_divisao'] ?? 'equitativa'
        );

        if ($result === true) {
            $this->redirectionUrl = "group/view/" . $postData['id_grupo'] . "?status=expense_added";
        } else {
            $this->redirectionUrl = "group/view/" . $postData['id_grupo'] . "?error=create_failed";
        }
    }
}


class RegistroDespesaTest extends TestCase
{
    private $defaultPostData = [
        'id_grupo' => 1,
        'id_pagador' => 1,
        'valor_total' => '75,50',
        'categoria' => 'Aluguel',
        'data_despesa' => '2025-10-17',
        'descricao' => 'Teste Válido',
        'tipo_divisao' => 'equitativa',
        'divisao_equitativa' => [1, 2]
    ];
    private $userId = 1;

    public function test_CT_ORG_U04_1_RegistroComSucesso()
    {
        $expenseModelMock = $this->createMock(Expense::class);
        $expenseModelMock->method('create')->willReturn(true);

        $groupModelMock = $this->createMock(Group::class);
        $groupModelMock->method('isUserMember')->willReturn(true); 

        $controller = new MockExpenseController($expenseModelMock, $groupModelMock);
        $controller->simularCreate($this->defaultPostData, $this->userId);

        $expectedUrl = "group/view/{$this->defaultPostData['id_grupo']}?status=expense_added";
        $this->assertEquals($expectedUrl, $controller->redirectionUrl);
    }

    public function test_CT_ORG_U04_2_FalhaNaConexaoDB()
    {
        $expenseModelMock = $this->createMock(Expense::class);
        $expenseModelMock->method('create')->willReturn(false);

        $groupModelMock = $this->createMock(Group::class);
        
        $controller = new MockExpenseController($expenseModelMock, $groupModelMock);
        $controller->simularCreate($this->defaultPostData, $this->userId);

        $expectedUrl = "group/view/{$this->defaultPostData['id_grupo']}?error=create_failed";
        $this->assertEquals($expectedUrl, $controller->redirectionUrl);
    }
    
    public function test_CT_ORG_U04_3_RejeitaCategoriaVazia()
    {
        $invalidData = $this->defaultPostData;
        $invalidData['categoria'] = '';
        
        $expenseModelMock = $this->createMock(Expense::class);
        $groupModelMock = $this->createMock(Group::class);
        
        $controller = new MockExpenseController($expenseModelMock, $groupModelMock);
        $controller->simularCreate($invalidData, $this->userId);
        
        $expectedUrl = "group/view/{$this->defaultPostData['id_grupo']}?error=validation";
        $this->assertEquals($expectedUrl, $controller->redirectionUrl);
    }

    public function test_CT_ORG_U04_4_RejeitaCategoriaExcedente()
    {
        $invalidData = $this->defaultPostData;
        $invalidData['categoria'] = str_repeat('A', 51);
        
        $expenseModelMock = $this->createMock(Expense::class);
        $groupModelMock = $this->createMock(Group::class);
        
        $controller = new MockExpenseController($expenseModelMock, $groupModelMock);
        $controller->simularCreate($invalidData, $this->userId);
        
        $errorMessage = urlencode("Categoria deve ter no máximo 50 caracteres. (RN-ORG04)");
        $expectedUrl = "group/view/{$this->defaultPostData['id_grupo']}?error={$errorMessage}";
        
        $this->assertEquals($expectedUrl, $controller->redirectionUrl);
        $expenseModelMock->expects($this->never())->method('create'); 
    }
}