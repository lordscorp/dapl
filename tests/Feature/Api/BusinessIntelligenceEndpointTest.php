<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Tests\Traits\PreparaDadosBi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\LogService;

class BusinessIntelligenceEndpointTest extends TestCase
{
    use PreparaDadosBi, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->popularBancoBi();

        // Evita que gravação de logs durante os testes E2E
        $this->mock(LogService::class, function ($mock) {
            $mock->shouldReceive('registrarDaSessao')->andReturnNull();
        });
    }

    public function test_endpoint_buscar_sql_incra_retorna_dados_corretos()
    {
        $this->getJson('/api/bi/buscarsql?sql_incra=135.241.0012-1')
             ->assertStatus(200)
             ->assertJsonPath('0.sql', '135.241.0012-1')
             ->assertJsonPath('0.processo', '0123.2024/0121323-4')
             ->assertJsonPath('0.SituacaoAssunto', 'Deferido');
    }

    public function test_endpoint_buscar_sql_incra_retorna_400_se_parametro_faltar()
    {
        $this->getJson('/api/bi/buscarsql')
             ->assertStatus(400)
             ->assertJsonPath('erro', 'Parâmetro sql_incra é obrigatório');
    }

    public function test_endpoint_buscar_processos_avancado_retorna_resultados()
    {
        $payload = [
            'dataInicio' => '2023-01-01',
            'assuntos' => ['Alvará de Aprovação de Edificação Nova'],
            'subprefeituras' => ['Sé']
        ];

        $this->postJson('/api/bi/buscarProcessos', $payload)
             ->assertStatus(200)
             ->assertJsonCount(1)
             ->assertJsonPath('0.sistema', 'Aprovação');
    }

    public function test_endpoint_listar_filtros_retorna_agrupamentos_corretos()
    {
        $this->getJson('/api/bi/listarFiltros')
             ->assertStatus(200)
             ->assertJsonPath('assuntos.0', 'Alvará de Aprovação de Edificação Nova')
             ->assertJsonPath('distritos.0', 'Bela Vista');
    }

    public function test_endpoint_buscar_por_processo_retorna_situacao_e_sistema_corretos()
    {
        $this->getJson('/api/bi/buscarPorProcesso?processo_sei=0123.2024/0121323-4')
             ->assertStatus(200)
             ->assertJsonPath('0.situacaoProcesso', 'Em Andamento')
             ->assertJsonPath('0.sistema', 'Aprovanet');
    }
}
