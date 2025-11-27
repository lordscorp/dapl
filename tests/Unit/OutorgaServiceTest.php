<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\OutorgaService;

class OutorgaServiceTest extends TestCase
{
    public function test_calculo_outorga_onerosa()
    {
        $service = new OutorgaService();
        $resultado = $service->calcularOutorga(1000, 2000, 1252, 0.4, 0.3);
        $this->assertEquals(75120, $resultado); // Exemplo: (1000/500)*200*1.2*1.1
    }

    public function test_calculos_oodc_verificados()
    {
        // 4556-21-SP-ALV	Alvará de Aprovação e Execução de Edificação Nova (HIS/ HMP)	Deferido	380	657,49	Sim	HIS	05509700327			200085	VILA CARRÃO
        $service = new OutorgaService();
        $valorMetroQuadrado = 2078.16;
        $fatorSocial = 0.8;
        $fatorPlanejamento = 0.6;
        $resultado = $service->calcularOutorga(380, 657.49, $valorMetroQuadrado, $fatorSocial, $fatorPlanejamento);
        $this->assertEquals(159978.64, $resultado); // Exemplo: (1000/500)*200*1.2*1.1
    }
}
