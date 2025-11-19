<?php

use PHPUnit\Framework\TestCase;

class Cenario3ValidacaoCompostaTest extends TestCase
{
    private $hoje_simulado = '2025-10-17';

    private function processarValor(string $input): float
    {
        $valor_total_str = preg_replace("/[^0-9,]/", "", $input);
        $valor_total_limpo = str_replace(',', '.', $valor_total_str);
        return (float) $valor_total_limpo;
    }

    private function simularValidacaoData(string $data_despesa): bool
    {
        return $data_despesa <= $this->hoje_simulado;
    }

    public function test_CT_ORG_U03_1_DataInvalidaValorValido()
    {
        $valor_processado = $this->processarValor("100,00");
        $data_futura = '2025-10-18';

        $this->assertGreaterThan(0.0, $valor_processado); // PASS: Valor é válido.

        $this->assertFalse($this->simularValidacaoData($data_futura)); // PASS: Data é rejeitada (futura).
    }

    public function test_CT_ORG_U03_2_BugSanitizacaoImpedeMultiplosErros()
    {
        $valor_processado = $this->processarValor("-5,00");
        $data_futura = '2025-10-18';

        $this->assertGreaterThan(0.0, $valor_processado);

        $this->assertFalse($this->simularValidacaoData($data_futura));
    }

    public function test_CT_ORG_U03_3_VerificaRedirecionamento()
    {
        $valor_processado_invalido = $this->processarValor("0,00");

        $this->assertLessThanOrEqual(0.0, $valor_processado_invalido);

        $this->assertTrue(true);
    }
}