<?php

namespace Tests\Unit;

use App\DTOs\ParametrosCalculoOutorgaDTO;
use App\Services\LogService;
use PHPUnit\Framework\TestCase;
use App\Services\OutorgaService;

class OutorgaServiceTest extends TestCase
{
    protected OutorgaService $service;

    public function setUp(): void
    {
        parent::setUp();

        $mockLogService = $this->createMock(LogService::class);

        $this->service = new OutorgaService($mockLogService);
        // $this->app->instance(LogService::class, $this->createMock(LogService::class));
    }
    
    public function test_calculo_outorga_onerosa()
    {
        $valorEsperado = 75120;

        $this->assertEquals(
            $valorEsperado, 
            $this->service->calcularOutorga(
                new ParametrosCalculoOutorgaDTO(1252, 0.4, 0.3, 1000, 2000)
            )
        );
    }

    public function test_calculos_oodc_verificados()
    {
        $valorEsperado = 159978.64;
        
        // 4556-21-SP-ALV	Alvará de Aprovação e Execução de Edificação Nova (HIS/ HMP)
        // Deferido	380	657,49	Sim	HIS	05509700327			200085	VILA CARRÃO
        $valorMetroQuadrado = 2078.16;
        $fatorSocial = 0.8;
        $fatorPlanejamento = 0.6;
        
        $this->assertEquals(
            $valorEsperado, 
            $this->service->calcularOutorga(
                new ParametrosCalculoOutorgaDTO($valorMetroQuadrado, $fatorSocial, $fatorPlanejamento, 380, 657.49)
            )
        );
    }
}
