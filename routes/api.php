<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Processos;

Route::get('/processoAValidar', [Processos::class, 'processoAValidar']);
Route::post('/validarProcesso', [Processos::class, 'validarProcesso']);


/*
Route::post('/validarProcesso', function(\Illuminate\Http\Request $request) {
    $data = $request->input('objProcesso');

    // Mapeamento dos nomes para colunas
    $mapaUso = [
        'HIS' => 'numHIS',
        'HMP' => 'numHMP',
        'EHIS' => 'numEHIS',
        'EHMP' => 'numEHMP',
        'R1' => 'numR1',
        'R2' => 'numR2',
    ];

    // Monta os dados para update
    $dadosUpdate = [
        'blocos' => (int) $data['blocos'],
        'pavimentos' => (int) $data['pavimentos'],
        'validado' => 1,
        'validando' => 0,
        'validadoEm' => now()
    ];

    // Adiciona os campos de uniCatUso conforme nome
    foreach ($data['uniCatUso'] as $item) {
        $nome = strtoupper($item['nome']);
        if (array_key_exists($nome, $mapaUso)) {
            $coluna = $mapaUso[$nome];
            $dadosUpdate[$coluna] = (int) $item['valor'];
        }
    }

    // Atualiza o registro
    DB::table('levantamentohis')
        ->where('autonum', $data['autonum'])
        ->update($dadosUpdate);

    return response()->json(['status' => 'ok', 'atualizado' => $dadosUpdate]);
});

Route::get('/processoAValidar', function (\Illuminate\Http\Request $request) {
    $rfValidador = $request->query('rfValidador');

    // Primeiro busca processo que esteja em validacao pendente
    $registro = DB::table('levantamentohis')
        ->select('autonum', 'processo', 'assunto', 'dtEmissao', 'doc_txt')
        ->where('rfValidador', $rfValidador)
        ->first();

    if (!$registro) {
        // Busca o primeiro registro com validando = false e validado = false, ordenado por dtEmissao desc
        $registro = DB::table('levantamentohis')
            ->select('autonum', 'codigoPedido', 'processo', 'assunto', 'dtEmissao', 'doc_txt')
            ->whereNull('validando')
            ->whereNull('validado')
            ->whereIn('assunto', [
                'Certificado de Conclusao'
            ])
            ->where(function ($query) {
                $query->where('doc_txt', 'like', '%H.M.P%')
                    ->orWhere('doc_txt', 'like', '%HMP%')
                    ->orWhere('doc_txt', 'like', '%H M P%')
                    ->orWhere('doc_txt', 'like', '%HIS%')
                    ->orWhere('doc_txt', 'like', '%H I S%')
                    ->orWhere('doc_txt', 'like', '%H.I.S%');
            })
            ->whereBetween('dtEmissao', ['2014-01-01', '2019-08-13'])
            ->orderByDesc('dtEmissao')
            ->limit(1)
            ->first();
    }

    function extrairLinha21($texto)
    {
        $linhas = preg_split('/\r\n|\r|\n/', $texto);
        return count($linhas) >= 21 ? trim($linhas[20]) : null;
    }


    if (!$registro) {
        return response()->json(['message' => 'Nenhum processo encontrado'], 404);
    }

    // SALVA REGISTRO ENCONTRADO COMO 'VALIDANDO'

    DB::table('levantamentohis')
        ->where('autonum', $registro->autonum)
        ->update([
            'validando' => 1,
            'rfValidador' => $rfValidador,
        ]);

    // Carrega doc_txt do Alvara de Aprovacao
    $docAprovacao = '';
    // TODO: carregar dados da tabela relacionada

    return response()->json([
        'objProcesso' => [
            'autonum'       => $registro->autonum,
            'categoria'     => extrairLinha21($registro->doc_txt),
            'processo'      => $registro->processo ?? '',
            'assunto'       => $registro->assunto ?? '',
            'dtEmissao'     => $registro->dtEmissao ?? '',
            'docAprovacao'  => $docAprovacao,
            'docConclusao'  => $registro->doc_txt ?? '',
            'uniCatUso'     => [(object)[]], // sempre vazio
        ]
    ]);
});
*/