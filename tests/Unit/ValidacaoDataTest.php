<?php

use PHPUnit\Framework\TestCase;

class Cenario2ValidacaoDataTest extends TestCase
{
    private $hoje_simulado = '2025-10-17';

    private function simularValidacaoData(string $data_despesa): bool
    {
        return $data_despesa <= $this->hoje_simulado;
    }

    public function test_CT_ORG_U02_1_RejeitaDataFutura()
    {
        $amanha = '2025-10-18';

        $this->assertFalse(
            $this->simularValidacaoData($amanha),
            "A data futura ($amanha) deveria ser rejeitada (retornar FALSE na validação)."
        );
    }

    public function test_CT_ORG_U02_2_AceitaDataLimiteHoje()
    {
        $hoje = $this->hoje_simulado;

        $this->assertTrue(
            $this->simularValidacaoData($hoje),
            "A data limite (HOJE: $hoje) deveria ser aceita (retornar TRUE na validação)."
        );
    }

    public function test_CT_ORG_U02_3_AceitaDataPassada()
    {
        $ontem = '2025-10-16';

        $this->assertTrue(
            $this->simularValidacaoData($ontem),
            "A data passada ($ontem) deveria ser aceita (retornar TRUE na validação)."
        );
    }

    public function test_CT_ORG_U02_4_AusenciaDeValidacaoDeFormato()
    {
        $data_invalida = '32/13/2025';

        $this->assertFalse(
            $this->simularValidacaoData($data_invalida),
            "O formato inválido foi rejeitado, mas pelo motivo errado (foi considerada data futura)."
        );
    }

    public function test_CT_ORG_U02_5_AceitaFormatoISO()
    {
        $data_iso_valida = '2025-01-01';

        $this->assertTrue(
            $this->simularValidacaoData($data_iso_valida),
            "O formato ISO válido ($data_iso_valida) deveria ser aceito."
        );
    }
}