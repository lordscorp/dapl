<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Tests\Traits\PreparaDadosOutorga;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OutorgaEndpointTest extends TestCase
{
    // PreparaDadosOutorga: Trait contendo os dados do empreendimento e para popular o mock do banco de dados
    // RefreshDatabase: Apaga os dados do banco após cada teste, garantindo que os testes não interfiram entre si
    use PreparaDadosOutorga, RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->popularBancoOutorga();
    }

    public function test_endpoint_consultar_valor_m2()
    {
        $valorEsperado = 2078.16;

        $this->getJson("/api/outorga/consultarValorM2?ano={$this->ano}&sql={$this->sql}&codlog={$this->codlog}")
             ->assertOk()
             ->assertJsonPath('valor_m2', $valorEsperado);
    }

    public function test_endpoint_consultar_fator_planejamento()
    {
        $valorEsperado = 0.8;

        $this->getJson("/api/outorga/consultarFatorPlanejamento?setor={$this->setor}&quadra={$this->quadra}")
             ->assertOk()
             ->assertJsonPath('fp', $valorEsperado);
    }

    public function test_endpoint_consultar_fator_social()
    {
        $valorEsperado = 1;

        $this->getJson("/api/outorga/consultarFatorSocial?uso={$this->uso}&area={$this->ac}")
             ->assertOk()
             ->assertJsonPath('fs', $valorEsperado);
    }

    public function test_endpoint_calcular_outorga()
    {
        $valorEsperado = 24095.02;

        $this->postJson('/api/outorga/calcularOutorga', [
            'at' => $this->at,
            'ac' => $this->ac,
            'v' => 1252,
            'fs' => 0.4,
            'fp' => 0.3
        ])
        ->assertOk()
        ->assertJsonPath('resultado', $valorEsperado);
    }


    public function test_endpoint_calculo_oodc_verificados()
    {
        $valorEsperado = 266631.07;

        // 1. OBTER v A PARTIR DO SQL e ano
        $vm2 = $this->getJson("/api/outorga/consultarValorM2?ano={$this->ano}&sql={$this->sql}&codlog={$this->codlog}")
                    ->assertOk()
                    ->json("valor_m2");
      
        // 2. OBTER fp A PARTIR DO SETOR E QUADRA
        $fp = $this->getJson("/api/outorga/consultarFatorPlanejamento?setor={$this->setor}&quadra={$this->quadra}")
                   ->assertOk()
                   ->json("fp");

        // 3. OBTER fs A PARTIR DO USO E ÁREA
        $fs = $this->getJson("/api/outorga/consultarFatorSocial?uso={$this->uso}&area={$this->ac}")
                   ->assertOk()
                   ->json("fs");

        // 4. CALCULAR A OUTORGA
        $this->postJson('/api/outorga/calcularOutorga', [
            'at' => $this->at,
            'ac' => $this->ac,
            'v' => $vm2,
            'fs' => $fs,
            'fp' => $fp
        ])
        ->assertOk()
        ->assertJsonPath('resultado', $valorEsperado);
    }
}
