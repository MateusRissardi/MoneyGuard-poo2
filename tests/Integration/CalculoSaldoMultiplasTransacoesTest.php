<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../app/model/Expense.php';

class MockBalanceServiceMulti
{
    private $expenseModel;

    public function __construct($expenseModel)
    {
        $this->expenseModel = $expenseModel;
    }

    public function obterSaldos(int $id_grupo)
    {
        return $this->expenseModel->getBalance($id_grupo);
    }
}


class Cenario10CalculoSaldoMultiplasTransacoesTest extends TestCase
{
    private $id_grupo = 30;
    private $p1 = 1;
    private $p2 = 2;
    private $p3 = 3;

    private function calcularSaldos(array $saldosDB)
    {
        $saldos = [];
        foreach ($saldosDB as $saldo) {
            $saldos[$saldo['id_usuario']] = round($saldo['total_pago'] - $saldo['total_consumido'], 2);
        }
        return $saldos;
    }

    public function test_CT_ORG_I03_1_DebitoAcumulativo()
    {
        $saldoEsperadoDB = [
            ['id_usuario' => $this->p1, 'total_pago' => 90.00, 'total_consumido' => 40.00],
            ['id_usuario' => $this->p2, 'total_pago' => 30.00, 'total_consumido' => 40.00],
            ['id_usuario' => $this->p3, 'total_pago' => 0.00, 'total_consumido' => 40.00],
        ];

        $expenseModelMock = $this->createMock(Expense::class);
        $expenseModelMock->method('getBalance')->willReturn($saldoEsperadoDB);

        $balanceService = new MockBalanceServiceMulti($expenseModelMock);
        $saldosRetornados = $balanceService->obterSaldos($this->id_grupo);
        $saldosCalculados = $this->calcularSaldos($saldosRetornados);

        $this->assertEquals(50.00, $saldosCalculados[$this->p1], "Saldo P1 deve ser +R$ 50.00");
        $this->assertEquals(-10.00, $saldosCalculados[$this->p2], "Saldo P2 deve ser -R$ 10.00");
        $this->assertEquals(-40.00, $saldosCalculados[$this->p3], "Saldo P3 deve ser -R$ 40.00");

        $this->assertEquals(0.00, array_sum($saldosCalculados), "A soma total dos saldos deve ser zero.");
    }

    public function test_CT_ORG_I03_2_CompensacaoDeSaldos()
    {
        $saldoEsperadoDB = [
            ['id_usuario' => $this->p1, 'total_pago' => 50.00, 'total_consumido' => 37.50],
            ['id_usuario' => $this->p2, 'total_pago' => 25.00, 'total_consumido' => 37.50],
        ];

        $expenseModelMock = $this->createMock(Expense::class);
        $expenseModelMock->method('getBalance')->willReturn($saldoEsperadoDB);

        $balanceService = new MockBalanceServiceMulti($expenseModelMock);
        $saldosRetornados = $balanceService->obterSaldos($this->id_grupo);
        $saldosCalculados = $this->calcularSaldos($saldosRetornados);

        $this->assertEquals(12.50, $saldosCalculados[$this->p1], "Saldo P1 deve ser +R$ 12.50");
        $this->assertEquals(-12.50, $saldosCalculados[$this->p2], "Saldo P2 deve ser -R$ 12.50");

        $this->assertEquals(0.00, array_sum($saldosCalculados), "A soma total dos saldos deve ser zero.");
    }

    public function test_CT_ORG_I03_3_ArredondamentoEDizimas()
    {
        $saldoEsperadoDB = [
            ['id_usuario' => $this->p1, 'total_pago' => 10.00, 'total_consumido' => 6.67], // 3.34+3.33
            ['id_usuario' => $this->p2, 'total_pago' => 0.00, 'total_consumido' => 6.66], // 3.33+3.33
            ['id_usuario' => $this->p3, 'total_pago' => 10.00, 'total_consumido' => 6.67], // 3.33+3.34
        ];

        $expenseModelMock = $this->createMock(Expense::class);
        $expenseModelMock->method('getBalance')->willReturn($saldoEsperadoDB);

        $balanceService = new MockBalanceServiceMulti($expenseModelMock);
        $saldosRetornados = $balanceService->obterSaldos($this->id_grupo);
        $saldosCalculados = $this->calcularSaldos($saldosRetornados);

        $this->assertEquals(3.33, $saldosCalculados[$this->p1]);
        $this->assertEquals(-6.66, $saldosCalculados[$this->p2]);
        $this->assertEquals(3.33, $saldosCalculados[$this->p3]);

        $this->assertEquals(0.00, array_sum($saldosCalculados), "A soma total dos saldos deve ser zero.");
    }

    public function test_CT_ORG_I03_4_MultiplosPagantesDiferentesTransacoes()
    {
        $id_grupo = 34;

        $saldoEsperadoDB = [
            ['id_usuario' => $this->p1, 'total_pago' => 60.00, 'total_consumido' => 35.00],
            ['id_usuario' => $this->p2, 'total_pago' => 30.00, 'total_consumido' => 35.00],
            ['id_usuario' => $this->p3, 'total_pago' => 0.00, 'total_consumido' => 20.00],
        ];

        $expenseModelMock = $this->createMock(Expense::class);
        $expenseModelMock->method('getBalance')->willReturn($saldoEsperadoDB);

        $balanceService = new MockBalanceServiceMulti($expenseModelMock);
        $saldosRetornados = $balanceService->obterSaldos($id_grupo);
        $saldosCalculados = $this->calcularSaldos($saldosRetornados);

        $this->assertEquals(25.00, $saldosCalculados[$this->p1], "Saldo P1 deve ser +R$ 25.00");
        $this->assertEquals(-5.00, $saldosCalculados[$this->p2], "Saldo P2 deve ser -R$ 5.00");
        $this->assertEquals(-20.00, $saldosCalculados[$this->p3], "Saldo P3 deve ser -R$ 20.00");

        $this->assertEquals(0.00, array_sum($saldosCalculados), "A soma total dos saldos deve ser zero.");
    }
}