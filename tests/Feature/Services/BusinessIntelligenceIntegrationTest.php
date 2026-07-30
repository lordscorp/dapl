<?php

namespace Tests\Integration;

use Tests\TestCase;
use Tests\Traits\PreparaDadosBi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\BusinessIntelligenceService;

class BusinessIntelligenceIntegrationTest extends TestCase
{
    use PreparaDadosBi, RefreshDatabase;

    protected BusinessIntelligenceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->popularBancoBi();
        
        $this->service = new BusinessIntelligenceService();
    }

    public function test_deve_buscar_processo_por_sql_incra_exato()
    {
        $resultado = $this->service->buscarPorSqlIncra('05503300121');

        $this->assertCount(1, $resultado);
        $this->assertEquals('055.033.0012-1', $resultado[0]['sql']);
        $this->assertEquals('2023-0.001.002-3', $resultado[0]['processo']);
        
        $this->assertStringContainsString('João Silva', $resultado[0]['interessados']);
    }

    public function test_deve_buscar_processos_por_filtros_de_data()
    {
        $filtros = [
            'dataInicio' => '2023-01-01',
            'dataFim' => '2023-12-31'
        ];

        $resultado = $this->service->buscarProcessos($filtros);

        $this->assertCount(1, $resultado);
        $this->assertEquals('Alvará de Aprovação', $resultado[0]->assunto);
    }
}
