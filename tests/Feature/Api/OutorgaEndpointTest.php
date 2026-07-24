<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OutorgaEndpointTest extends TestCase
{
    // Isolamento do DB ao rodar o teste
    use RefreshDatabase;

    // Dados do empreendimento para os testes
    protected string $uso;
    protected float $at;
    protected float $ac;
    protected int $ano;
    protected string $sql;
    protected string $setor;
    protected string $quadra;
    protected string $codlog;

    public function setUp(): void
    {
        parent::setUp();

        // Inicialização das variáveis de teste
        $this->uso = 'COMUM';
        $this->at = 380;
        $this->ac = 657.49;
        $this->ano = 2021;
        $this->sql = '05509700327';
        $this->setor = '055';
        $this->quadra = '097';
        $this->codlog = '200085';

        // Preparação do mock do banco de dados para os testes
        Schema::create('oodc_quadro14_vm2_2020', function (Blueprint $table) {
            $table->string('setor', 3);
            $table->string('quadra', 3);
            $table->string('sq', 6)->nullable();
            $table->string('codlog', 10);
            $table->decimal('vm2', 10, 2)->nullable();
        });

        DB::table('oodc_quadro14_vm2_2020')->insert([
            'setor' => '055',
            'quadra' => '097',
            'sq' => '055097',
            'codlog' => '200085',
            'vm2' => 2078.16
        ]);

        Schema::create('sq_macroareas', function (Blueprint $table) {
            $table->id();
            $table->string('cd_quadra_fiscal', 50)->nullable();
            $table->string('cd_setor_fiscal', 50)->nullable();
            $table->string('tx_macro_divisao_pde', 50)->nullable();
            $table->string('nm_perimetro_divisao_pde', 50)->nullable();
        });

        DB::table('sq_macroareas')->insert([
            'cd_quadra_fiscal' => '097',
            'cd_setor_fiscal' => '055',
            'tx_macro_divisao_pde' => 'Macroárea de Qualificação da Urbanização',
            'nm_perimetro_divisao_pde' => ''
        ]);

        Schema::create('oodc_quadro6_fp', function (Blueprint $table) {
            $table->id();
            $table->string('macroarea', 150)->nullable();
            $table->string('perimetro', 150)->nullable();
            $table->decimal('fp_R', 3, 1)->nullable();
        });

        DB::table('oodc_quadro6_fp')->insert([
            'macroarea' => 'Macroárea de Qualificação da Urbanização',
            'perimetro' => '',
            'fp_R' => 0.8
        ]);

        Schema::create('oodc_quadro5_fs', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_uso', 50)->nullable();
            $table->decimal('area_minima', 6, 2)->nullable();
            $table->decimal('area_maxima', 6, 2)->nullable();
            $table->decimal('fs', 3, 1)->nullable();
        });

        DB::table('oodc_quadro5_fs')->insert([
            'tipo_uso' => 'COMUM',
            'area_minima' => 70.01,
            'area_maxima' => 9999.99,
            'fs' => 1
        ]);

    }

    public function test_endpoint_consultar_valor_m2()
    {
        $valorEsperado = 2078.16;

        $response = $this->getJson("/api/outorga/consultarValorM2?ano={$this->ano}&sql={$this->sql}&codlog={$this->codlog}");

        $response->assertStatus(200)
                 ->assertJson(['valor_m2' => $valorEsperado]);
    }

    public function test_endpoint_consultar_fator_planejamento()
    {
        $valorEsperado = 0.8;

        $response = $this->getJson("/api/outorga/consultarFatorPlanejamento?setor={$this->setor}&quadra={$this->quadra}");

        $response->assertStatus(200)
                 ->assertJson(['fp' => $valorEsperado]);
    }

    public function test_endpoint_consultar_fator_social()
    {
        $valorEsperado = 1;

        $response = $this->getJson("/api/outorga/consultarFatorSocial?uso={$this->uso}&area={$this->ac}");

        $response->assertStatus(200)
                 ->assertJson(['fs' => $valorEsperado]);
    }

        public function test_endpoint_calcular_outorga()
    {
        $response = $this->postJson('/api/outorga/calcularOutorga', [
            'at' => $this->at,
            'ac' => $this->ac,
            'v' => 1252,
            'fs' => 0.4,
            'fp' => 0.3
        ]);

        $response->assertStatus(200)
            ->assertJson(['resultado' => 24095.02]);
    }


    public function test_endpoint_calculo_oodc_verificados()
    {
        $valorEsperado = 266631.07;

        // 1. OBTER v A PARTIR DO SQL e ano
        $responseVm2 = $this->getJson("/api/outorga/consultarValorM2?ano={$this->ano}&sql={$this->sql}&codlog={$this->codlog}");
        $responseVm2->assertStatus(200);

        $vm2 = $responseVm2->json("valor_m2");
      
        // 2. OBTER fp A PARTIR DO SETOR E QUADRA
        $responseFp = $this->getJson("/api/outorga/consultarFatorPlanejamento?setor={$this->setor}&quadra={$this->quadra}");
        $responseFp->assertStatus(200);

        $fp = $responseFp->json("fp");

        // 3. OBTER fs A PARTIR DO USO E ÁREA
        $responseFs = $this->getJson("/api/outorga/consultarFatorSocial?uso={$this->uso}&area={$this->ac}");
        $responseFs->assertStatus(200);

        $fs = $responseFs->json("fs");

        // 4. CALCULAR A OUTORGA
        $response = $this->postJson('/api/outorga/calcularOutorga', [
            'at' => $this->at,
            'ac' => $this->ac,
            'v' => $vm2,
            'fs' => $fs,
            'fp' => $fp
        ]);
        
        $response->assertStatus(200)
            ->assertJson(['resultado' => $valorEsperado]);
    }
}
