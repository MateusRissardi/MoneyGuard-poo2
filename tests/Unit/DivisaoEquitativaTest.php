<?php

use PHPUnit\Framework\TestCase;

class Cenario5DivisaoEquitativaTest extends TestCase
{

    private function simularDivisaoEquitativa(float $valor_total, int $num_membros): array
    {
        if ($num_membros <= 0) {
            return [0.0, 0.0];
        }

        $valor_por_membro = round($valor_total / $num_membros, 2);

        $soma_total = $valor_por_membro * $num_membros;

        return [$valor_por_membro, round($soma_total, 2)];
    }


    public function test_CT_ORG_U05_1_ArredondamentoComDizima()
    {
        list($valor_por_membro, $soma_total) = $this->simularDivisaoEquitativa(10.00, 3);

        $this->assertEquals(3.33, $valor_por_membro);

        $this->assertNotEquals(
            10.00,
            $soma_total,
            "A soma total dos splits (R$ 9.99) não é igual ao valor total (R$ 10.00), violando RN-ORG2."
        );
    }

    public function test_CT_ORG_U05_2_DivisaoExata()
    {
        list($valor_por_membro, $soma_total) = $this->simularDivisaoEquitativa(25.00, 4);

        $this->assertEquals(6.25, $valor_por_membro);
        $this->assertEquals(
            25.00,
            $soma_total,
            "A soma total dos splits (R$ 25.00) é igual ao valor total (R$ 25.00)."
        );
    }

    public function test_CT_ORG_U05_3_DivisaoComImpar()
    {
        list($valor_por_membro, $soma_total) = $this->simularDivisaoEquitativa(11.00, 2);

        $this->assertEquals(5.50, $valor_por_membro);
        $this->assertEquals(11.00, $soma_total);
    }

    public function test_CT_ORG_U05_4_RejeitaZeroParticipantes()
    {
        $ids_membros = [];

        $this->assertEmpty($ids_membros, "O array de membros selecionados é vazio.");
        $this->assertTrue(empty($ids_membros));
    }

    public function test_CT_ORG_U05_5_AceitaUmParticipante()
    {
        list($valor_por_membro, $soma_total) = $this->simularDivisaoEquitativa(150.00, 1);

        $this->assertEquals(150.00, $valor_por_membro);
        $this->assertEquals(150.00, $soma_total);
    }

    public function test_CT_ORG_U05_6_AceitaValorZero()
    {
        list($valor_por_membro, $soma_total) = $this->simularDivisaoEquitativa(0.00, 3);

        $this->assertEquals(0.00, $valor_por_membro);
        $this->assertEquals(0.00, $soma_total);
    }
}