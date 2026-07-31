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
        $valorEsperado = 2078.16;
        
        // Deve retornar o valor da tabela de 2020, pois não existe tabela para 2021
        $ano = 2021;        
        $this->assertEquals(
            $valorEsperado, 
            $this->service->consultarValorM2($ano, $this->sql, $this->codlog), 
            'O valor retornado para o valor do m² não é o esperado.'
        );
    }

    public function test_consultar_fator_planejamento()
    {
        $valorEsperado = 0.8;

        $this->assertEquals(
            $valorEsperado,
            $this->service->consultarFatorPlanejamento($this->setor, $this->quadra),
            'O fator de planejamento retornado não é o esperado.'
        );
    }

    public function test_consultar_fator_social()
    {
        $valorEsperado = 1.0;

        $this->assertEquals(
            $valorEsperado, 
            $this->service->consultarFatorSocial($this->uso, $this->ac), 
            'O fator de social retornado não é o esperado.'
        );
    }
}
