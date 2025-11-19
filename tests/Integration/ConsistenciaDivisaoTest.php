<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../app/model/Expense.php'; 
require_once __DIR__ . '/../../app/model/Group.php'; 

class MockExpenseControllerIntegration
{
    private $expenseModel;
    public $redirectionUrl = null;
    
    private $membros = [['id_usuario' => 1], ['id_usuario' => 2]];

    public function __construct($expenseModel)
    {
        $this->expenseModel = $expenseModel;
    }

    public function simularCreateManual(array $postData)
    {
        $id_grupo = $postData['id_grupo'];
        $valor_total_float = $postData['valor_total_float'];
        $divisao_manual = $postData['divisao_manual'] ?? [];
        $soma_manual = 0.0;
        $divisao = [];

        foreach ($this->membros as $membro) {
            $id_membro = $membro['id_usuario'];
            $valor_str = $divisao_manual[$id_membro] ?? '0';
            $valor_str_limpo = preg_replace("/[^-0-9,]/", "", $valor_str); // Permite '-' para o teste RN-ORG05
            $valor_membro = (float) str_replace(',', '.', $valor_str_limpo);

            if ($valor_membro < 0) {
                $this->redirectionUrl = "group/view/{$id_grupo}?error=" . urlencode("Divisão manual não pode ter valores negativos. (RN-ORG05)");
                return;
            }

            if ($valor_membro > 0) {
                $divisao[] = ['id_participante' => $id_membro, 'valor_devido' => $valor_membro];
                $soma_manual += $valor_membro;
            }
        }

        if (abs($soma_manual - $valor_total_float) > 0.01) {
            $this->redirectionUrl = "group/view/{$id_grupo}?error=" . urlencode("Soma da divisão manual (R$ {$soma_manual}) não bate com o Valor Total (R$ {$valor_total_float}). (RN-ORG02)");
            return;
        }
        
        if (empty($divisao)) {
            $this->redirectionUrl = "group/view/{$id_grupo}?error=" . urlencode("A despesa deve ter pelo menos um participante. (RN-ORG09)");
            return;
        }

        $result = $this->expenseModel->create(
            $id_grupo, $postData['id_pagador'], $valor_total_float, $postData['categoria'], 
            $postData['data_despesa'], $postData['descricao'], $divisao, null, 'manual'
        );

        if ($result === true) {
            $this->redirectionUrl = "group/view/{$id_grupo}?status=expense_added";
        } else {
            $this->redirectionUrl = "group/view/{$id_grupo}?error=create_failed";
        }
    }
}


class Cenario8ConsistenciaDivisaoTest extends TestCase
{
    private $baseData = [
        'id_grupo' => 10,
        'id_pagador' => 1,
        'valor_total_float' => 50.00,
        'categoria' => 'Teste',
        'data_despesa' => '2025-10-17',
        'descricao' => 'Divisão Manual',
        'tipo_divisao' => 'manual',
    ];

    public function test_CT_ORG_I01_1_BloqueiaSomaMenor()
    {
        $postData = $this->baseData;
        $postData['divisao_manual'] = [1 => '20,00', 2 => '20,00']; 
        
        $expenseModelMock = $this->createMock(Expense::class);
        $controller = new MockExpenseControllerIntegration($expenseModelMock);
        $controller->simularCreateManual($postData);

        $errorMessage = urlencode("Soma da divisão manual (R$ 40) não bate com o Valor Total (R$ 50). (RN-ORG02)");
        $expectedUrl = "group/view/{$postData['id_grupo']}?error={$errorMessage}";
        
        $this->assertEquals($expectedUrl, $controller->redirectionUrl);
        $expenseModelMock->expects($this->never())->method('create');
    }

    public function test_CT_ORG_I01_2_BloqueiaSomaMaior()
    {
        $postData = $this->baseData;
        $postData['divisao_manual'] = [1 => '30,00', 2 => '30,00']; 
        
        $expenseModelMock = $this->createMock(Expense::class);
        $controller = new MockExpenseControllerIntegration($expenseModelMock);
        $controller->simularCreateManual($postData);

        $errorMessage = urlencode("Soma da divisão manual (R$ 60) não bate com o Valor Total (R$ 50). (RN-ORG02)");
        $expectedUrl = "group/view/{$postData['id_grupo']}?error={$errorMessage}";
        
        $this->assertEquals($expectedUrl, $controller->redirectionUrl);
        $expenseModelMock->expects($this->never())->method('create');
    }

    public function test_CT_ORG_I01_3_PermiteSomaIgual()
    {
        $postData = $this->baseData;
        $postData['divisao_manual'] = [1 => '25,00', 2 => '25,00']; 
        
        $expenseModelMock = $this->createMock(Expense::class);
        
        $expenseModelMock->expects($this->once())
                         ->method('create')
                         ->willReturn(true); 
        
        $controller = new MockExpenseControllerIntegration($expenseModelMock);
        $controller->simularCreateManual($postData);

        $expectedUrl = "group/view/{$postData['id_grupo']}?status=expense_added";
        $this->assertEquals($expectedUrl, $controller->redirectionUrl);
    }
    
    public function test_CT_ORG_I01_4_BloqueiaDivisaoVazia()
{
    $postData = $this->baseData;
    $postData['divisao_manual'] = [1 => '0,00', 2 => '0,00']; 
    
    $expenseModelMock = $this->createMock(Expense::class);
    
    // Expectativa: NUNCA chama o método create, pois a validação de RN-ORG02 deve bloquear.
    $expenseModelMock->expects($this->never())
                     ->method('create');
                         
    $controller = new MockExpenseControllerIntegration($expenseModelMock);
    $controller->simularCreateManual($postData);

    // MENSAGEM ESPERADA CORRIGIDA: A despesa falha porque a SOMA (0.00) é diferente do TOTAL (50.00).
    $errorMessage = urlencode("Soma da divisão manual (R$ 0) não bate com o Valor Total (R$ 50). (RN-ORG02)");
    $expectedUrl = "group/view/{$postData['id_grupo']}?error={$errorMessage}";
    
    $this->assertEquals($expectedUrl, $controller->redirectionUrl);
}
}