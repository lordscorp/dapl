<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ValidadosExport;

class Processos extends Controller
{
    /// GET
    public function dadosDashboard()
    {

        $path = storage_path('app/idsHisHmp1419.txt');
        $ids = array_filter(array_map('trim', file($path)));
        $dados = DB::table('levantamentohis')
            ->whereIn('autonum', $ids)
            ->selectRaw('
            COUNT(autonum) as totalHisHmp,
            SUM(CASE WHEN validando = 1 THEN 1 ELSE 0 END) as totalValidando,
            SUM(CASE WHEN validado = 1 THEN 1 ELSE 0 END) as totalValidado
        ')
            ->first();

        // return response()->json($dados);


        $validadores = DB::table('levantamentohis')
            ->whereIn('autonum', $ids)
            ->whereNotNull('rfValidador')
            ->select('rfValidador', DB::raw('COUNT(*) as total'))
            ->groupBy('rfValidador')
            ->orderByDesc('total')
            ->pluck('total', 'rfValidador');

        // Converte stdClass para array e adiciona os validadores
        $dadosArray = (array) $dados;
        $dadosArray['validadores'] = $validadores;

        return response()->json($dadosArray);
    }

    public function mockDadosDashboard()
    {
        // Gera um número total aleatório entre 50 e 200
        $totalHisHmp = rand(50, 200);

        // Gera um número de validando entre 0 e 20
        $totalValidando = rand(0, min(20, $totalHisHmp));

        // Gera um número de validado entre 0 e (total - validando)
        $totalValidado = rand(0, $totalHisHmp - $totalValidando);

        $dados = (object) [
            'totalHisHmp' => $totalHisHmp,
            'totalValidando' => $totalValidando,
            'totalValidado' => $totalValidado
        ];

        return response()->json($dados);
    }

    public function validarProcesso(Request $request)
    {
        $data = $request->input('objProcesso');

        // Mapeamento dos nomes para colunas
        $mapaUso = [
            'HIS' => 'num_HIS',
            'HMP' => 'num_HMP',
            'EHIS' => 'num_EHIS',
            'EHMP' => 'num_EHMP',
            'R1' => 'num_R1',
            'R2' => 'num_R2',
            'nRa'    => 'num_nRa',
            'nR1'    => 'num_nR1',
            'nR2'    => 'num_nR2',
            'nR3'    => 'num_nR3',
            'Ind1a'  => 'num_Ind1a',
            'Ind1b'  => 'num_Ind1b',
            'Ind2'   => 'num_Ind2',
            'Ind3'   => 'num_Ind3',
            'INFRA'  => 'num_INFRA',

        ];

        // Monta os dados para update
        $dadosUpdate = [
            'blocos'             => (int) $data['blocos'],
            'pavimentos'         => (int) $data['pavimentos'],
            'validado'           => 1,
            'validando'          => 0,
            'validadoEm'         => now(),
            'amparoLegal'        => $data['amparoLegal'] ?? null,
            'usoDoImovel'        => $data['usoDoImovel'] ?? null,
            'constaOutorga'      => isset($data['constaOutorga']) && $data['constaOutorga'] ? 1 : 0,
            'P_QTD_TERR_REAL'    => number_format((float) ($data['areaTotal'] ?? 0), 2, '.', ''),
            'P_QTD_AREA_CNSR'    => number_format((float) ($data['areaConstruida'] ?? 0), 2, '.', ''),
            'P_QTD_AREA_CMPL'    => number_format((float) ($data['areaComputavel'] ?? 0), 2, '.', ''),
            'subprefeitura'      => $data['subprefeitura'] ?? '',
            'zoneamento'         => $data['zoneamento'] ?? '',
            'proprietario'       => $data['proprietario'] ?? '',
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
    }

    public function processoAValidar(Request $request)
    {
        $rfValidador = $request->query('rfValidador');
        $camposSelect = [
            'autonum',
            'codigoPedido',
            'sql_incra',
            'processo',
            'assunto',
            'dtEmissao',
            'doc_txt',
            'codigoPedidoReferenciado',
            'P_QTD_TERR_REAL',
            'P_QTD_AREA_CNSR',
            'P_QTD_AREA_CMPL'
        ];

        // Primeiro busca processo que esteja em validacao pendente
        $path = storage_path('app/idsHisHmp1419.txt');
        $ids = array_filter(array_map('trim', file($path)));

        $registro = DB::table('levantamentohis')
            ->select($camposSelect)
            ->whereIn('autonum', $ids)
            ->where('validando', 1)
            ->where('rfValidador', $rfValidador)
            ->first();

        if (!$registro) {
            // Busca o primeiro registro com validando = false e validado = false, ordenado por dtEmissao desc
            $registro = DB::table('levantamentohis')
                ->select($camposSelect)
                ->whereIn('autonum', $ids)
                ->whereNull('validando')
                ->whereNull('validado')
                ->orderByDesc('dtEmissao')
                ->limit(1)
                ->first();
        }

        $docsRelacionados = DB::table('levantamentohis')
            ->select('assunto', 'dtEmissao', 'doc_txt')
            ->where('sql_incra', $registro->sql_incra)
            ->where('autonum', '<>', $registro->autonum)
            ->orderBy('dtEmissao', 'desc')
            ->get()
            ->map(function ($doc) {
                return [
                    'assunto' => $doc->assunto,
                    'dtEmissao' => $doc->dtEmissao,
                    'doc_txt' => $doc->doc_txt,
                ];
            });

        function extrairLinha($texto, $linha)
        {
            $linhas = preg_split('/\r\n|\r|\n/', $texto);
            return count($linhas) >= $linha ? trim($linhas[$linha - 1]) : null;
        }

        function extrairAmparoLegal(string $texto): ?string
        {
            // Captura "AMPARO LEGAL:" seguido de qualquer conteúdo, seja na mesma linha ou na próxima
            if (preg_match('/AMPARO LEGAL\s*:\s*(?:\r?\n)?(.*)/i', $texto, $matches)) {
                return trim($matches[1]);
            }

            return null;
        }

        function extrairUsoDoImovel(string $texto): ?string
        {
            $texto = strtoupper($texto);
            // Captura "USO DO IMOVEL:" seguido de qualquer conteúdo, seja na mesma linha ou na próxima
            if (preg_match('/USO DO IMOVEL\s*:\s*(?:\r?\n)?(.*)/i', $texto, $matches)) {
                return trim($matches[1]);
            }

            if (preg_match('/USO DO IMÓVEL\s*:\s*(?:\r?\n)?(.*)/i', $texto, $matches)) {
                return trim($matches[1]);
            }

            return null;
        }

        function extrairZoneamento($texto)
        {
            // Primeiro tenta encontrar "ZONEAMENTO ATUAL"
            if (preg_match('/ZONEAMENTO\s+ATUAL\s*[:\-]*\s*([A-Za-z].*)/i', $texto, $match)) {
                return trim($match[1]);
            }

            // Se não encontrar, procura por "ZONEAMENTO" que NÃO seja "ZONEAMENTO ANTERIOR"
            if (preg_match_all('/ZONEAMENTO(?!\s+ANTERIOR)\s*[:\-]*\s*([A-Za-z].*)/i', $texto, $matches)) {
                // Retorna a primeira ocorrência válida
                return trim($matches[1][0]);
            }

            // Se nada for encontrado, retorna null
            return null;
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
        $docCodReferenciado = DB::table('levantamentohis')
            ->select('assunto', 'dtEmissao', 'doc_txt', 'P_QTD_TERR_REAL', 'P_QTD_AREA_CNSR', 'P_QTD_AREA_CMPL')
            ->where('codigoPedido', $registro->codigoPedidoReferenciado)
            ->limit(1)
            ->first();

        $txtAmparoLegal = '';
        $txtZoneamento = '';
        $txtUsoDoImovel = '';

        try {
            $txtAmparoLegal = extrairAmparoLegal($registro->doc_txt);
            if (empty($txtAmparoLegal)) {
                $txtAmparoLegal = extrairAmparoLegal($docCodReferenciado->doc_txt);
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

        try {
            $txtUsoDoImovel = extrairUsoDoImovel($registro->doc_txt);
            if (empty($txtUsoDoImovel)) {
                $txtUsoDoImovel = extrairUsoDoImovel($docCodReferenciado->doc_txt);
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

        try {
            $txtZoneamento = extrairZoneamento($registro->doc_txt);
            if (empty($txtZoneamento)) {
                $txtZoneamento = extrairZoneamento($docCodReferenciado->doc_txt);
            }
            if (empty($txtZoneamento)) {
                foreach ($docsRelacionados as $doc) {
                    $zoneamento = extrairZoneamento($doc['doc_txt']);
                    if ($zoneamento) {
                        $txtZoneamento = $zoneamento;
                        break;
                    }
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

        $areaTotal = '';
        $areaConstruida = '';
        $areaComputavel = '';

        try {
            $areaTotal = $registro->P_QTD_TERR_REAL ?? $docCodReferenciado->P_QTD_TERR_REAL;
            $areaConstruida = $registro->P_QTD_AREA_CNSR ?? $docCodReferenciado->P_QTD_AREA_CNSR;
            $areaComputavel = $registro->P_QTD_AREA_CMPL ?? $docCodReferenciado->P_QTD_AREA_CMPL;
        } catch (\Throwable $th) {
            //throw $th;
        }

        return response()->json([
            'objProcesso' => [
                'autonum'       => $registro->autonum,
                'categoria'     => extrairLinha($registro->doc_txt, 21),
                'proprietario'     => extrairLinha($registro->doc_txt, 8),
                'processo'      => $registro->processo ?? '',
                'assunto'       => $registro->assunto ?? '',
                'dtEmissao'     => $registro->dtEmissao ?? '',
                'docCodReferenciado'  => $docCodReferenciado->doc_txt ?? '',
                'docConclusao'  => $registro->doc_txt ?? '',
                'uniCatUso'     => [(object)[]], // sempre vazio,
                'docsRelacionados' => $docsRelacionados,
                'sqlIncra'      => $registro->sql_incra,
                'amparoLegal'   => $txtAmparoLegal,
                'areaTotal'     => $areaTotal,
                'areaConstruida'     => $areaConstruida,
                'areaComputavel'     => $areaComputavel,
                'usoDoImovel'   => $txtUsoDoImovel,
                'zoneamento'    => $txtZoneamento
            ]
        ]);
    }


    public function exportarValidados()
    {
        $registros = DB::table('levantamentohis')
            ->where(function ($query) {
                $query->where('validado', 1)
                    ->orWhere('validando', 1);
            })
            ->get(['processo', 'codigoPedido', 'assunto', 'dtEmissao', 'codigoPedidoReferenciado', 'documentoReferenciado', 'sql_incra', 'doc_txt', 'validando', 'validado', 'validadoEm', 'rfValidador', 'blocos', 'pavimentos', 'amparoLegal', 'usoDoImovel', 'constaOutorga', 'zoneamento', 'num_HIS', 'num_HMP', 'num_R1', 'num_R2', 'num_nR1', 'num_nR2', 'proprietario']);

        return Excel::download(new ValidadosExport($registros), 'processosValidados.xlsx');
    }
}
