<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\LogService;
use App\Services\OutorgaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class OutorgaServiceIntegrationTest extends TestCase
{
    protected OutorgaService $service;
    
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
        // Ao rodar isso em Tests\TestCase, o Laravel "sobe" a aplicação e conecta no DB
        parent::setUp();

        $mockLogService = $this->createMock(LogService::class);
        $this->service = new OutorgaService($mockLogService);

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
