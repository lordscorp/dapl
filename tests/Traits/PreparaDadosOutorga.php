<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

trait PreparaDadosOutorga
{
    protected string $uso = 'COMUM';
    protected float $at = 380;
    protected float $ac = 657.49;
    protected int $ano = 2021;
    protected string $sql = '05509700327';
    protected string $setor = '055';
    protected string $quadra = '097';
    protected string $codlog = '200085';

    protected function popularBancoOutorga()
    {
        // 1. Tabela para consulta de VM2
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

        // 2. Tabelas para consulta de FP
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

        // 3. Tabela para consulta de FS
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
}
