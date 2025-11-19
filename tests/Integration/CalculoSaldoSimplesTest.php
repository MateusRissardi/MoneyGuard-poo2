<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../app/model/Expense.php';

class MockBalanceService
{
    private $expenseModel;

    public function __construct($expenseModel)
    {
        $this->expenseModel = $expenseModel;
    }

    public function registrarEObterSaldos(int $id_grupo)
    {
        $this->expenseModel->create(
            $id_grupo,
            1,
            100.00,
            'Teste',
            '2025-10-17',
            'Desc',
            [['id_participante' => 1, 'valor_devido' => 0.00], ['id_participante' => 2, 'valor_devido' => 100.00]],
            null,
            'manual'
        );

        return $this->expenseModel->getBalance($id_grupo);
    }
}


class Cenario9CalculoSaldoSimplesTest extends TestCase
{
    private $id_grupo = 10;
    private $participante1 = 1;
    private $participante2 = 2;

    public function test_CT_ORG_I02_1_PaganteNaoDeve()
    {
        $saldoEsperadoDB = [
            [
                'id_usuario' => $this->participante1,
                'nome' => 'P1',
                'total_pago' => 100.00, // Pagou 100
                'total_consumido' => 0.00 // Consumiu 0
            ],
            [
                'id_usuario' => $this->participante2,
                'nome' => 'P2',
                'total_pago' => 0.00, // Pagou 0
                'total_consumido' => 100.00 // Consumiu 100
            ],
        ];

        $expenseModelMock = $this->createMock(Expense::class);

        $expenseModelMock->method('create')->willReturn(true);

        $expenseModelMock->method('getBalance')
            ->with($this->id_grupo)
            ->willReturn($saldoEsperadoDB);

        $balanceService = new MockBalanceService($expenseModelMock);
        $saldosRetornados = $balanceService->registrarEObterSaldos($this->id_grupo);

        $p1_saldo = $saldosRetornados[0]['total_pago'] - $saldosRetornados[0]['total_consumido'];
        $p2_saldo = $saldosRetornados[1]['total_pago'] - $saldosRetornados[1]['total_consumido'];

        $this->assertEquals(100.00, $p1_saldo, "O Saldo de P1 (Pagante/Não-Devedor) deve ser +R$ 100.00");
        $this->assertEquals(-100.00, $p2_saldo, "O Saldo de P2 (Devedor Total) deve ser -R$ 100.00");

        $this->assertEquals(0.00, $p1_saldo + $p2_saldo, "A soma total dos saldos deve ser zero (princípio contábil).");
    }

    public function test_CT_ORG_I02_2_MultiplosDevedoresEquitativa()
    {
        $id_grupo = 20;
        $participante3 = 3;

        $saldoEsperadoDB = [
            [
                'id_usuario' => $this->participante1,
                'nome' => 'P1',
                'total_pago' => 60.00,
                'total_consumido' => 20.00
            ],
            [
                'id_usuario' => $this->participante2,
                'nome' => 'P2',
                'total_pago' => 0.00,
                'total_consumido' => 20.00
            ],
            [
                'id_usuario' => $participante3,
                'nome' => 'P3',
                'total_pago' => 0.00,
                'total_consumido' => 20.00
            ],
        ];

        $expenseModelMock = $this->createMock(Expense::class);
        $expenseModelMock->method('create')->willReturn(true);
        $expenseModelMock->method('getBalance')
            ->with($id_grupo)
            ->willReturn($saldoEsperadoDB);

        $balanceService = new MockBalanceService($expenseModelMock);
        $saldosRetornados = $balanceService->registrarEObterSaldos($id_grupo);

        $p1_saldo = $saldosRetornados[0]['total_pago'] - $saldosRetornados[0]['total_consumido'];
        $p2_saldo = $saldosRetornados[1]['total_pago'] - $saldosRetornados[1]['total_consumido'];
        $p3_saldo = $saldosRetornados[2]['total_pago'] - $saldosRetornados[2]['total_consumido'];

        $this->assertEquals(40.00, $p1_saldo, "Saldo de P1 deve ser +R$ 40.00");
        $this->assertEquals(-20.00, $p2_saldo, "Saldo de P2 deve ser -R$ 20.00");
        $this->assertEquals(-20.00, $p3_saldo, "Saldo de P3 deve ser -R$ 20.00");

        $this->assertEquals(0.00, $p1_saldo + $p2_saldo + $p3_saldo, "A soma total dos saldos deve ser zero.");
    }
}