<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

trait PreparaDadosBi
{
    public function popularBancoBi()
    {
        // 1. Criar as tabelas para os testes do BI Service
        Schema::create('prata_sql_incra', function (Blueprint $table) {
            $table->id('id_prata_sql_incra');
            $table->string('sql_incra');
            $table->unsignedBigInteger('id_prata_assunto');
            $table->string('processo')->nullable();
        });

        Schema::create('prata_assunto', function (Blueprint $table) {
            $table->id('id_prata_assunto');
            $table->string('sistema')->nullable();
            $table->string('processo')->nullable();
            $table->string('assunto')->nullable();
            $table->string('SituacaoAssunto')->nullable();
            $table->date('dtPedidoProtocolo')->nullable();
            $table->string('subprefeitura')->nullable();
            $table->string('distrito')->nullable();
            $table->string('origem_subprefeitura')->nullable();
            $table->string('aditivo')->nullable();
            $table->string('protocolo')->nullable();
        });

        Schema::create('prata_interessado', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_prata_assunto');
            $table->string('nomeInteressado');
            $table->string('atribuicao');
        });

        Schema::create('prata_processo', function (Blueprint $table) {
            $table->id('id_prata_processo');
            $table->string('processo');
            $table->string('sistema');
            $table->date('dtAutuacaoProcesso');
            $table->string('situacaoProcesso')->nullable();
            $table->string('tipoprocesso')->nullable();
        });

        Schema::create('prata_endereco', function (Blueprint $table) {
            $table->id('id_prata_endereco');
            $table->string('processo')->nullable();
            $table->string('codlog');
        });


        // 2. Inserir dados de mock para os testes
        DB::table('prata_assunto')->insert([
            'id_prata_assunto' => 1,
            'sistema' => 'Aprovação',
            'processo' => '0123.2024/0121323-4',
            'assunto' => 'Alvará de Aprovação de Edificação Nova',
            'SituacaoAssunto' => 'Deferido',
            'dtPedidoProtocolo' => '2024-02-28',
            'subprefeitura' => 'Sé',
            'distrito' => 'Bela Vista',
            'protocolo' => '32109-24-SP-ALV',
        ]);

        DB::table('prata_sql_incra')->insert([
            'id_prata_sql_incra' => 100,
            'sql_incra' => '055.033.0012-1',
            'processo' => '0123.2024/0121323-4',
            'id_prata_assunto' => 1
        ]);

        DB::table('prata_interessado')->insert([
            ['id_prata_assunto' => 1, 'nomeInteressado' => 'João Silva', 'atribuicao' => 'Proprietário'],
            ['id_prata_assunto' => 1, 'nomeInteressado' => 'Maria Souza', 'atribuicao' => 'Autora']
        ]);
        
        DB::table('prata_processo')->insert([
            [
                'id_prata_processo' => 1,
                'sistema' => 'nome do sistema',
                'processo' => '0123.2024/0121323-4',
                'dtAutuacaoProcesso' => '2024-02-28',
                'situacaoProcesso' => 'Deferido',
                'tipoprocesso' => 'tipo do processo'
            ]
        ]);
        
        DB::table('prata_endereco')->insert([
            'id_prata_endereco' => 44,
            'processo' => '0123.2024/0121323-4',
            'codlog' => '15104-8'
        ]);

    }
}
