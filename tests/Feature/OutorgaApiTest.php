<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutorgaApiTest extends TestCase
{

    public function test_endpoint_calculo_outorga()
    {
        $response = $this->postJson('/api/outorga', [
            'at' => 1000,
            'ac' => 2000,
            'v' => 1252,
            'fs' => 0.4,
            'fp' => 0.3
        ]);

        $response->assertStatus(200)
            ->assertJson(['resultado' => 75120]);
    }

    public function test_endpoint_calculo_oodc_verificados()
    {
        return;
        // ALTERNAR PARA TESTES e2e
        $at = 380;
        $ac = 657.49;
        $ano = 2021;
        $vm2 = $fs = $fp = 0;
        $sql = '05509700327';
        $codlog = '200085';
        $valorEsperado = 159978.64;


        //      OBTER v, fs e fp A PARTIR DO SQL e ano
        $responseVm2 = $this->getJson("/api/consultarValorM2?ano={$ano}&sql={$sql}&codlog={$codlog}");
        $responseVm2->assertStatus(200);

        $vm2 = $responseVm2->json("valor_m2");

        // Espera-se que o valor do metro quadrado seja 2078.16
        $this->assertEquals(2078.16, $vm2, 'O valor do metro quadrado desse empreendimento em 2021 deve ser 2078.16');

        $response = $this->postJson('/api/outorga', [
            'at' => $at,
            'ac' => $ac,
            'v' => $vm2,
            'fs' => $fs,
            'fp' => $fp
        ]);
        
        $response->assertStatus(200)
            ->assertJson(['resultado' => $valorEsperado]);
        /*
        $valorTerreno = 2078.16;
        $fatorSocial = 0.8;
        $fatorPlanejamento = 0.6;
        $resultado = $service->calcularOutorga(380, 657.49, $valorTerreno, $fatorSocial, $fatorPlanejamento);
        $this->assertEquals(159978.64, $resultado); // Exemplo: (1000/500)*200*1.2*1.1
        */
    }
}
