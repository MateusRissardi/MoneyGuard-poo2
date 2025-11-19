<?php

use PHPUnit\Framework\TestCase;

class Cenario7ValidacaoDivisaoManualTest extends TestCase
{
    /**
     * Simula a lógica de sanitização e validação de divisão manual do ExpenseController.
     * Corrigida para permitir o sinal negativo '-' na limpeza (sanitização) 
     * para que a validação RN-ORG05 seja testada corretamente.
     */
    private function simularValidacaoManual(float $valor_total, array $divisoes_manuais)
    {
        $soma_manual = 0.0;
        
        foreach ($divisoes_manuais as $valor_str) {
            // 1. Simulação da Limpeza: permite '-', números e vírgula para testar a RN-ORG05.
            $valor_str_limpo = preg_replace("/[^-0-9,]/", "", $valor_str); 
            $valor_membro = (float) str_replace(',', '.', $valor_str_limpo);

            // 2. Validação RN-ORG05: Valor não pode ser negativo
            if ($valor_membro < 0) {
                return "Erro: Divisão manual não pode ter valores negativos. (RN-ORG05)";
            }

            $soma_manual += $valor_membro;
        }

        // 3. Validação RN-ORG2: A soma deve ser igual ao total (com tolerância de 0.01)
        if (abs($soma_manual - $valor_total) > 0.01) {
            return "Erro: Soma da divisão manual (R$ {$soma_manual}) não bate com o Valor Total (R$ {$valor_total}). (RN-ORG02)";
        }
        
        return true;
    }

    public function test_CT_ORG_U07_1_RejeitaSomaMenor()
    {
        $valor_total = 100.00;
        $divisoes = ['pA' => '50,00', 'pB' => '40,00']; 

        $resultado = $this->simularValidacaoManual($valor_total, $divisoes);

        $this->assertStringStartsWith("Erro:", $resultado, "A soma menor deveria ser rejeitada.");
    }

    public function test_CT_ORG_U07_2_RejeitaSomaMaior()
    {
        $valor_total = 100.00;
        $divisoes = ['pA' => '60,00', 'pB' => '50,00']; 

        $resultado = $this->simularValidacaoManual($valor_total, $divisoes);

        $this->assertStringStartsWith("Erro:", $resultado, "A soma maior deveria ser rejeitada.");
    }

    public function test_CT_ORG_U07_3_AceitaSomaIgual()
    {
        $valor_total = 100.00;
        $divisoes = ['pA' => '70,00', 'pB' => '30,00']; 

        $resultado = $this->simularValidacaoManual($valor_total, $divisoes);

        $this->assertTrue($resultado);
    }

    public function test_CT_ORG_U07_4_RejeitaValorNegativo()
    {
        $valor_total = 100.00;
        // -10,00 é preservado, ativando a RN-ORG05 (mesmo que a soma total seja 100.00).
        $divisoes = ['pA' => '110,00', 'pB' => '-10,00']; 

        $resultado = $this->simularValidacaoManual($valor_total, $divisoes);

        $this->assertEquals("Erro: Divisão manual não pode ter valores negativos. (RN-ORG05)", $resultado, 
            "A divisão com valor negativo deveria ser rejeitada pela regra RN-ORG05."
        );
    }
}