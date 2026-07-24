<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Services\LogService;
use App\Services\OutorgaService;
use Tests\Traits\PreparaDadosOutorga;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OutorgaIntegrationTest extends TestCase
{
    use PreparaDadosOutorga, RefreshDatabase;

    protected OutorgaService $service;
    
    public function setUp(): void
    {
        // Ao rodar isso em Tests\TestCase, o Laravel "sobe" a aplicação e conecta no DB
        parent::setUp();

        $mockLogService = $this->createMock(LogService::class);
        $this->service = new OutorgaService($mockLogService);

        $this->popularBancoOutorga();
    }

    public function test_consultar_valor_m2_retorna_valor_correto_para_2021()
    {
        // Deve retornar o valor da tabela de 2020, pois não existe tabela para 2021
        $resultado = $this->service->consultarValorM2(2021, $this->sql, $this->codlog);
        
        $this->assertEquals(2078.16, $resultado, 'O valor retornado do banco não é o esperado.');
    }

    public function test_consultar_fator_planejamento()
    {
        $valorEsperado = 0.8;

        $resultado = $this->service->consultarFatorPlanejamento($this->setor, $this->quadra);

        $this->assertEquals($valorEsperado, $resultado, 'O fator de planejamento retornado não é o esperado.');
    }

    public function test_consultar_fator_social()
    {
        $valorEsperado = 1.0;

        $resultado = $this->service->consultarFatorSocial($this->uso, $this->ac);

        $this->assertEquals($valorEsperado, $resultado, 'O fator de social retornado não é o esperado.');
    }
}
