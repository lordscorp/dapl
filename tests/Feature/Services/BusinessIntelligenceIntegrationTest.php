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
        $this->assertEquals('0123.2024/0121323-4', $resultado[0]['processo']);
        
        $this->assertStringContainsString('João Silva', $resultado[0]['interessados']);
    }

    public function test_deve_buscar_processos_por_filtros_de_data()
    {
        $filtros = [
            'dataInicio' => '2023-01-01',
            'dataFim' => '2024-12-31'
        ];

        $resultado = $this->service->buscarProcessos($filtros);

        $this->assertCount(1, $resultado);
        $this->assertEquals('Alvará de Aprovação de Edificação Nova', $resultado[0]->assunto);
    }

    public function test_deve_buscar_por_processo_pelo_numero() {
        $resultado = $this->service->buscarPorProcesso('0123.2024/0121323-4');

        $this->assertCount(1, $resultado);
        $this->assertEquals('0123.2024/0121323-4', $resultado[0]['processo']);
        $this->assertEquals('nome do sistema', $resultado[0]['sistema']);
        $this->assertEquals('2024-02-28', $resultado[0]['dtAutuacaoProcesso']);
        $this->assertEquals('Deferido', $resultado[0]['situacaoProcesso']);
        $this->assertEquals('tipo do processo', $resultado[0]['tipoprocesso']);
        $this->assertEquals('055.033.0012-1', $resultado[0]['sql_incra']);
        $this->assertEquals('32109-24-SP-ALV', $resultado[0]['protocolo']);
        $this->assertEquals('2024-02-28', $resultado[0]['dtPedidoProtocolo']);
        $this->assertEquals('Deferido', $resultado[0]['SituacaoAssunto']);
        $this->assertEquals('15104-8', $resultado[0]['codlog']);
    }
}
