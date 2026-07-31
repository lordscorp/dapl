<?php

namespace Tests\Feature\Api;

use App\Services\LogService;
use Tests\TestCase;
use Tests\Traits\PreparaDadosBi;
use Tests\Traits\PreparaDadosOutorga;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AntaresEndpointTest extends TestCase
{
    use PreparaDadosBi, PreparaDadosOutorga, RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->popularBancoBi();
        $this->popularBancoOutorga();

        // Evita que gravação de logs durante os testes E2E
        $this->mock(LogService::class, function ($mock) {
            $mock->shouldReceive('registrarDaSessao')->andReturnNull();
        });
    }

    public function test_endpoint_antares_procurar_processo() {
        $this->getJson('/api/antares/procurarProcesso?processo_sei=0123.2024/0121323-4')
             ->assertStatus(200)
             ->assertJsonPath('processo.protocolo', '32109-24-SP-ALV')
             ->assertJsonPath('outorga.parametrosDeCalculo.valorM2', 3475.20);
    }

    public function test_endpoint_antares_calcular_outorga()
    {
        $payload = [
            'processoSei' => '0123.2024/0121323-4',
            'areaTerreno' => 2500.00,
            'areaComputavel' => 7000.00
        ];

        $this->postJson('/api/antares/calcularOutorga', $payload)
             ->assertStatus(200)
             ->assertJsonCount(1)
             ->assertJsonPath('outorga.valorOutorga', 2792571.43);
    }
}
