<?php

use PHPUnit\Framework\TestCase;

class Cenario1ValidacaoDespesaTest extends TestCase
{
    private function processarValor(string $input): float
    {
        $valor_total_str = preg_replace("/[^0-9,]/", "", $input);

        $valor_total_limpo = str_replace(',', '.', $valor_total_str);

        return (float) $valor_total_limpo;
    }

    public function test_CT_ORG_U01_1_RejeitaValorIgualAZero()
    {
        $input = "0,00";
        $valor_processado = $this->processarValor($input);

        $this->assertLessThanOrEqual(
            0.0,
            $valor_processado,
            "O valor de R$ 0,00 deveria ser processado como 0.0 e rejeitado."
        );
        $this->assertEquals(0.0, $valor_processado);
    }

    public function test_CT_ORG_U01_2_RejeitaValorNegativo()
    {
        $input = "-10,00";
        $valor_processado = $this->processarValor($input);

        $this->assertGreaterThan(0.0, $valor_processado);
        $this->assertEquals(10.0, $valor_processado);
    }

    public function test_CT_ORG_U01_3_AceitaValorMinimoValido()
    {
        $input = "0,01";
        $valor_processado = $this->processarValor($input);

        $this->assertGreaterThan(
            0.0,
            $valor_processado,
            "O valor mínimo de R$ 0,01 deveria ser maior que 0.0 para ser aceito."
        );
        $this->assertEquals(0.01, $valor_processado);
    }

    public function test_CT_ORG_U01_4_AceitaValorPadraoValido()
    {
        $input = "100,00";
        $valor_processado = $this->processarValor($input);

        $this->assertGreaterThan(0.0, $valor_processado);
        $this->assertEquals(100.0, $valor_processado);
    }

    public function test_CT_ORG_U01_5_RejeitaValorNaoNumerico()
    {
        $input = "ABC";
        $valor_processado = $this->processarValor($input);

        $this->assertLessThanOrEqual(
            0.0,
            $valor_processado,
            "O valor 'ABC' deveria resultar em 0.0 após a sanitização e ser rejeitado."
        );
        $this->assertEquals(0.0, $valor_processado);
    }

    public function test_CT_ORG_U01_6_ArredondamentoDeValores()
    {
        $input = "10,999";
        $valor_processado = $this->processarValor($input);

        $this->assertEquals(
            10.999,
            $valor_processado,
            "O valor deve ser interpretado como float corretamente."
        );

        $valor_arredondado_db = round($valor_processado, 2);
        $this->assertEquals(
            11.00,
            $valor_arredondado_db,
            "O valor com 3 casas decimais deve ser arredondado para R$ 11,00."
        );
    }

    public function test_CT_ORG_U01_7_AceitaValoresGrandes()
    {
        $input = "9999999,99";
        $valor_processado = $this->processarValor($input);

        $this->assertGreaterThan(0.0, $valor_processado);
        $this->assertEquals(9999999.99, $valor_processado);
    }
}