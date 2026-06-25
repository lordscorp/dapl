<?php

namespace App\Http\Controllers\Ft3;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ValidadosExport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;


class Ft3Processos extends Controller
{
    public const TABELA = 'empreendimentos';
    /// GET
    public function dadosDashboard()
    {
        return 'Endpoint funcional';

        $dados = DB::table(self::TABELA)
            ->where('ft1ValidadoEm' != NULL)
            ->selectRaw('
            COUNT(id) as totalHisHmp,
            SUM(CASE WHEN validando = 1 THEN 1 ELSE 0 END) as totalValidando,
            SUM(CASE WHEN validado = 0 THEN 1 ELSE 0 END) as totalPendente,
            SUM(CASE WHEN validado = 1 THEN 1 ELSE 0 END) as totalValidado
        ')
            ->first();

        $validando = DB::table(self::TABELA)
            ->select(['rfValidador', 'id', 'processo', 'sql_INCRA'])
            ->where('validando', 1)
            ->get();

        $validadores = DB::table(self::TABELA)
            ->whereNotNull('rfValidador')
            ->select('rfValidador', DB::raw('COUNT(*) as total'))
            ->groupBy('rfValidador')
            ->orderByDesc('total')
            ->pluck('total', 'rfValidador');

        // Converte stdClass para array e adiciona os validadores
        $dadosArray = (array) $dados;
        $dadosArray['validadores'] = $validadores;
        $dadosArray['validando'] = $validando;

        return response()->json($dadosArray);
    }

    public function dadosDashboardFt2()
    {
        $path = storage_path('app/idsHisHmp20202026_mistos.txt');

        $ids = array_filter(array_map('trim', file($path)));
        $dados = DB::table(self::TABELA)
            ->whereIn('id', $ids)
            ->selectRaw('
            COUNT(id) as totalHisHmp,
            SUM(CASE WHEN validando = 1 THEN 1 ELSE 0 END) as totalValidando,
            SUM(CASE WHEN COALESCE(validado, 0) = 0 THEN 1 ELSE 0 END) as totalPendente,
            SUM(CASE WHEN validado = 1 THEN 1 ELSE 0 END) as totalValidado
        ')
            ->first();

        // return response()->json($dados);

        $validando = DB::table(self::TABELA)
            ->select(['rfValidador', 'id', 'NumeroAD', 'SQL'])
            ->where('validando', 1)
            ->where(function ($q) {
                $q->whereNull('plantaSemCategoria')
                    ->orWhere('plantaSemCategoria', '!=', 1);
            })
            ->get();

        $validadores = DB::table(self::TABELA)
            ->whereIn('id', $ids)
            ->whereNotNull('rfValidador')
            ->where('validado', 1)
            ->select('rfValidador', DB::raw('COUNT(*) as total'))
            ->groupBy('rfValidador')
            ->orderByDesc('total')
            ->pluck('total', 'rfValidador');

        // Converte stdClass para array e adiciona os validadores
        $dadosArray = (array) $dados;
        $dadosArray['validadores'] = $validadores;
        $dadosArray['validando'] = $validando;

        return response()->json($dadosArray);
    }

    public function adicionarProcessoAListaNegra(Request $request)
    {
        $idProcesso = $request->query('id');
        $rfValidador = $request->query('rfValidador');

        if (!$idProcesso) {
            return response()->json(['erro' => 'ID do processo não informado'], 400);
        }

        DB::transaction(function () use ($idProcesso, $rfValidador) {

            // 1. Atualiza o processo na tbl_planurb
            DB::table(self::TABELA)
                ->where('id', $idProcesso)
                ->update([
                    'plantaSemCategoria' => 1
                ]);

            // 2. Insere log
            DB::table('logs')->insert([
                'acao'       => 'Lista negra',
                'detalhes'   => "Processo [$idProcesso] adicionado à lista negra por [$rfValidador]",
                'rf'         => $rfValidador,
                'nome'       => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        });

        return response()->json(['sucesso' => true]);
    }

    // ATRIBUIR PROCESSO
    public function atribuirProcesso(Request $request)
    {

        $request->validate([
            'rfValidador' => 'required|string',
            'numeroAD'    => 'required|string|min:5',
        ]);

        $rfSolicitante = $request->input('rfSolicitante');
        $rfValidador = $request->input('rfValidador');
        $numeroAD = $request->input('numeroAD');

        DB::transaction(function () use ($numeroAD, $rfValidador, $rfSolicitante) {

            // 1. Atualiza o processo na tbl_planurb
            DB::table(self::TABELA)
                ->where('numeroAD', 'like', "%$numeroAD%")
                ->update([
                    'plantaSemCategoria' => null,
                    'validando' => 1,
                    'rfValidador' => $rfValidador
                ]);

            // 2. Insere log
            DB::table('logs')->insert([
                'acao'       => 'Atribuir Processo',
                'detalhes'   => "Processo [$numeroAD] atribuido a [$rfValidador] por [$rfSolicitante]",
                'rf'         => $rfSolicitante,
                'nome'       => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        });

        return response()->json(['sucesso' => true]);
    }
    // FIM ATRIBUIR

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
        DB::table(self::TABELA)
            ->where('id', $data['id'])
            ->update($dadosUpdate);

        return response()->json(['status' => 'ok', 'atualizado' => $dadosUpdate]);
    }

    public function validarProcessoUnidades(Request $request)
    {
        $data = $request->input('objProcesso');

        // Monta os dados para update
        $dadosUpdate = [
            'NumBlocos'             => (int) $data['NumBlocos'],
            'NumPavimentos'         => (int) $data['NumPavimentos'],
            'validado'           => 1,
            'validando'          => 0,
            'validadoEm'         => now(),
            'plantaExplicitaUnidades'   => !empty($data['plantaExplicitaUnidades']) ? 1 : 0,
            'listaBlocos'           => $data['listaBlocos']
        ];

        // Atualiza o registro
        DB::table(self::TABELA)
            ->where('id', $data['id'])
            ->update($dadosUpdate);

        return response()->json(['status' => 'ok', 'atualizado' => $dadosUpdate]);
    }
// CAMPOS DA TABELA empreendimentos:
// id, grupoAssuntoReferenciado, sqlIncra, codlog, nomeInteressado, endereco, usoDoimovel, num_HIS, num_HIS1, num_HIS2, num_HMP, num_EHIS, num_EHMP, num_R1, num_R2, listaBlocos, plantaExplicitaUnidades, plantaSemCategoria
    public function processoUnidadesAValidar(Request $request)
    {
        $rfValidador = $request->query('rfValidador');
        $path = storage_path('app/idsHisHmp20142019_mistos.txt');
        $ids = array_filter(array_map('trim', file($path)));
        $camposSelect = [
            'id',
            'grupoAssuntoReferenciado',
            'sqlIncra',
            'codlog',
            'blocos',
            'pavimentos',
            'num_HIS',
            'num_HIS1',
            'num_HIS2',
            'num_HMP',
            'num_EHIS',
            'num_R2',
            'listaBlocos', 
            'plantaExplicitaUnidades', 
            'plantaSemCategoria'
        ];

        $registro = DB::table(self::TABELA)
            ->select($camposSelect)
            ->whereIn('id', $ids)
            ->where('rfValidador', $rfValidador)
            ->where('validando', 1)
            ->where(function ($q) {
                $q->whereNull('plantaSemCategoria')
                    ->orWhere('plantaSemCategoria', '!=', 1);
            })
            ->first();

        // SE NÃO HOUVER ALGUM EM VALIDAÇÃO, PROCURA ALGUM QUE TENHA SIDO ANALISADO PELO USUÁRIO
        /*
        if (!$registro) {
            $rf = substr($rfValidador, 1, 6);

            $registro = DB::table(self::TABELA)
                ->select($camposSelect)
                ->whereIn('id', $ids)
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->whereNull('validando')
                            ->orWhere('validando', 0);
                    })->where(function ($q) {
                        $q->whereNull('validado')
                            ->orWhere('validado', 0);
                    });
                })
                ->where(function ($query) use ($rf) {
                    $query
                        ->whereRaw(
                            "SUBSTRING_INDEX(SUBSTRING_INDEX(ResponsavelAnalise, 'RF:', -1), ' ', 1) = ?",
                            [$rf]
                        )
                        ->orWhereRaw(
                            "SUBSTRING_INDEX(SUBSTRING_INDEX(ResponsavelDespacho, 'RF:', -1), ' ', 1) = ?",
                            [$rf]
                        );
                })
                ->orderByRaw("
                        CASE
                            WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(ResponsavelAnalise, 'RF:', -1), ' ', 1) = ? THEN 1
                            WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(ResponsavelDespacho, 'RF:', -1), ' ', 1) = ? THEN 2
                            ELSE 3
                        END
                    ", [$rf, $rf])
                ->first();
        }
        */

        // SE NÃO HOUVER NENHUM DO USUÁRIO, PEGA O PRÓXIMO DA FILA
        if (!$registro) {
            $registro = DB::table(self::TABELA)
                ->select($camposSelect)
                ->whereIn('id', $ids)
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->whereNull('validando')
                            ->orWhere('validando', 0);
                    })->where(function ($q) {
                        $q->whereNull('validado')
                            ->orWhere('validado', 0);
                    });
                })
                ->orderByDesc('areaConstruida')
                ->first();

            if (!$registro) {
                return response()->json([]);
            }
        }

        $registroVinculado = null;
        $camposSelectVinculado = [
            'id',
            'grupoAssuntoReferenciado',
            'sqlIncra',
            'codlog',
            'blocos',
            'pavimentos',
            'num_HIS',
            'num_HIS1',
            'num_HIS2',
            'num_HMP',
            'num_EHIS',
            'num_R2',
            'listaBlocos', 
            'plantaExplicitaUnidades', 
            'plantaSemCategoria'
        ];

        try {
            // 1) Tenta encontrar primeiro "Modificativo"
            $registroVinculado = DB::table('levantamentohis')
                ->select($camposSelectVinculado)
                ->where('sql_incra', $registro->SQL)          // mesmo Setor/Quadra/Lote
                ->where('Assunto', 'like', '%Modificativo%')
                ->first();

            // 2) Se não encontrou, tenta "Aprovação"
            if (!$registroVinculado) {
                $registroVinculado = DB::table('levantamentohis')
                    ->select($camposSelectVinculado)
                    ->where('sql_incra', $registro->SQL)
                    ->where('id', '<>', $registro->id)
                    ->where('Assunto', 'like', '%Aprova%')
                    ->first();
            }
        } catch (\Throwable $e) {
            // throw ($e);
        }


        // SALVA REGISTRO ENCONTRADO COMO 'VALIDANDO'
        DB::table(self::TABELA)
            ->where('id', $registro->id)
            ->update([
                'validando' => 1,
                'rfValidador' => $rfValidador,
            ]);

        return response()->json([
            'objProcesso' => $registro,
            'objProcessoVinculado' => $registroVinculado
        ]);
    }

    public function processoAValidar(Request $request)
    {
        $rfValidador = $request->query('rfValidador');
        $camposSelect = [
            'id',
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

        $registro = DB::table(self::TABELA)
            ->select($camposSelect)
            ->whereIn('id', $ids)
            ->where('validando', 1)
            ->where('rfValidador', $rfValidador)
            ->first();

        if (!$registro) {
            $registro = DB::table(self::TABELA)
                ->select($camposSelect)
                ->whereIn('id', $ids)
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->whereNull('validando')
                            ->orWhere('validando', 0);
                    })->where(function ($q) {
                        $q->whereNull('validado')
                            ->orWhere('validado', 0);
                    });
                })
                ->orderByRaw('CAST(P_QTD_AREA_CNSR AS DECIMAL(10,2)) DESC')
                ->first();

            // Busca o primeiro registro com validando = false e validado = false, ordenado por dtEmissao desc
            // $registro = DB::table(self::TABELA)
            //     ->select($camposSelect)
            //     ->whereIn('id', $ids)
            //     ->whereNull('validando')
            //     ->whereNull('validado')
            //     ->orderByDesc('dtEmissao')
            //     ->limit(1)
            //     ->first();

            if (!$registro) {
                return response()->json(['message' => 'Nenhum processo encontrado'], 404);
            }
        }

        $docsRelacionados = DB::table(self::TABELA)
            ->select('assunto', 'dtEmissao', 'doc_txt')
            ->where('sql_incra', $registro->sql_incra)
            ->where('id', '<>', $registro->id)
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
            // Captura o bloco após "AMPARO LEGAL:" até a próxima linha em branco
            if (preg_match('/AMPARO LEGAL\s*:\s*(?:\r?\n)?((?:(?!\r?\n\r?\n).*\S.*(?:\r?\n)?)+)/i', $texto, $matches)) {
                $conteudo = trim($matches[1]);

                // Remove tudo antes da primeira ocorrência da palavra "LEI"
                if (preg_match('/LEI.*/is', $conteudo, $leiMatch)) {
                    return trim($leiMatch[0]);
                }
            }

            return null;
        }


        function extrairAmparoLegal_bkp(string $texto): ?string
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

        DB::table(self::TABELA)
            ->where('id', $registro->id)
            ->update([
                'validando' => 1,
                'rfValidador' => $rfValidador,
            ]);

        // Carrega doc_txt do Alvara de Aprovacao
        $docCodReferenciado = DB::table(self::TABELA)
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
                'id'       => $registro->id,
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
        $registros = DB::table(self::TABELA)
            ->where(function ($query) {
                $query->where('validado', 1)
                    ->orWhere('validando', 1);
            })
            ->get(['id', 'Assunto', 'NumeroAD', 'NumeroSEI', 'Tipologia', 'ZonaDeUso', 'Status', 'NumTotalUnidades', 'DataCriacao', 'DataAutuacao', 'DataDeferimento', 'SQL', 'INCRAs', 'AreaPublica', 'Endereco', 'SubPrefeitura', 'AreaTerreno', 'AreaContruidaTotal', 'AreaExistente', 'AreaAConstruir', 'AreaADemolir', 'NumBlocos', 'NumPavimentos', 'NumUnidadesResidenciais', 'NumUnidadesHIS', 'NumUnidadesHIS1', 'NumUnidadesHIS2', 'NumUnidadesHMP', 'NumUnidadesR2hR2v', 'AtividadesNR', 'Proprietario', 'NaturezaProprietario', 'LinkProcessoAD', 'DataUltimaVersao', 'Reaberto', 'ResponsavelAnalise', 'ResponsavelDespacho', 'InteressadosDocumentos', 'AlturaTotal', 'GabaritoTotal', 'AreaEstacionamentoResidencial', 'AreaEstacionamentoNaoResidencial', 'AreaEstacionamentoTotal', 'validando', 'validado', 'rfValidador', 'listaBlocos', 'validadoEm', 'plantaExplicitaUnidades']);

        return Excel::download(new ValidadosExport($registros), 'processosValidados.xlsx');
    }

    public function exportarValidadosFt1()
    {
        $registros = DB::table(self::TABELA)
            ->where(function ($query) {
                $query->where('validado', 1)
                    ->orWhere('validando', 1);
            })
            ->get(['processo', 'codigoPedido', 'assunto', 'dtEmissao', 'codigoPedidoReferenciado', 'documentoReferenciado', 'sql_incra', 'doc_txt', 'validando', 'validado', 'validadoEm', 'rfValidador', 'blocos', 'pavimentos', 'amparoLegal', 'usoDoImovel', 'constaOutorga', 'zoneamento', 'num_HIS', 'num_HMP', 'num_R1', 'num_R2', 'num_nR1', 'num_nR2', 'proprietario']);

        return Excel::download(new ValidadosExport($registros), 'processosValidados.xlsx');
    }


    public function exportarExcelListaBlocos()
    {
        $registros = DB::table(self::TABELA)
            ->select('id', 'listaBlocos')
            ->whereNotNull('listaBlocos')
            ->get();

        foreach ($registros as $registro) {

            $blocos = json_decode($registro->listaBlocos, true);
            if (!is_array($blocos)) {
                continue;
            }

            Excel::store(
                new class($blocos) implements WithMultipleSheets {

                    private array $blocos;

                    public function __construct(array $blocos)
                    {
                        $this->blocos = $blocos;
                    }

                    public function sheets(): array
                    {
                        $sheets = [];

                        foreach ($this->blocos as $index => $bloco) {

                            $sheets[] = new class($bloco, $index) implements FromArray, WithTitle, WithEvents {

                                private array $bloco;
                                private int $index;

                                public function __construct(array $bloco, int $index)
                                {
                                    $this->bloco = $bloco;
                                    $this->index = $index;
                                }

                                public function title(): string
                                {
                                    return 'Bloco' . ($this->index + 1);
                                }

                                public function array(): array
                                {
                                    $linhas = [];

                                    $listaPavimentos = $this->bloco['listaPavimentos'] ?? [];
                                    $pavimentosNegativos = (int)($this->bloco['pavimentosNegativos'] ?? 0);

                                    // Cabeçalho
                                    $maxUnidades = 0;
                                    foreach ($listaPavimentos as $p) {
                                        $maxUnidades = max($maxUnidades, count($p['listaUnidades'] ?? []));
                                    }

                                    $header = ['Pavimento'];
                                    for ($i = 1; $i <= $maxUnidades; $i++) {
                                        $header[] = 'Unidade ' . $i;
                                    }
                                    $linhas[] = $header;

                                    // Labels
                                    $labels = [];

                                    if ($pavimentosNegativos > 0) {
                                        for ($i = $pavimentosNegativos; $i >= 1; $i--) {
                                            $labels[] = 'Pavimento -' . $i;
                                        }
                                    }

                                    $labels[] = 'Térreo';
                                    $pavPos = 1;

                                    foreach ($listaPavimentos as $i => $pav) {
                                        if (!isset($labels[$i])) {
                                            $labels[$i] = 'Pavimento ' . $pavPos++;
                                        }

                                        $linha = [$labels[$i]];

                                        foreach ($pav['listaUnidades'] ?? [] as $unidade) {
                                            $linha[] = $unidade['cat'] ?? '';
                                        }

                                        $linhas[] = $linha;
                                    }

                                    return $linhas;
                                }

                                public function registerEvents(): array
                                {
                                    return [
                                        AfterSheet::class => function (AfterSheet $event) {

                                            $cores = [
                                                'HIS1' => 'FF88EEAA',
                                                'HIS2' => 'FF88DDEE',
                                                'HMP'  => 'FFCCBBEE',
                                                'R2v'  => 'FFEEAAAA',
                                                'R2h'  => 'FFFF5555',
                                            ];

                                            $sheet = $event->sheet->getDelegate();
                                            $linhaInicial = 2; // pula cabeçalho
                                            $ultimaLinha = $sheet->getHighestRow();
                                            $ultimaColuna = $sheet->getHighestColumn();

                                            for ($row = $linhaInicial; $row <= $ultimaLinha; $row++) {
                                                for ($col = 'B'; $col <= $ultimaColuna; $col++) {

                                                    $celula = $col . $row;
                                                    $valor = trim((string)$sheet->getCell($celula)->getValue());

                                                    if (isset($cores[$valor])) {
                                                        $sheet->getStyle($celula)->getFill()->setFillType(Fill::FILL_SOLID);
                                                        $sheet->getStyle($celula)->getFill()->getStartColor()->setARGB($cores[$valor]);
                                                    }
                                                }
                                            }
                                        }
                                    ];
                                }
                            };
                        }

                        return $sheets;
                    }
                },
                "id{$registro->id}.xlsx",
                'public'
            );
        }

        return response()->json(['status' => 'ok']);
    }


    public function exportarExcelListaBlocosSemHeader()
    {
        $registros = DB::table(self::TABELA)
            ->select('id', 'listaBlocos')
            ->whereNotNull('listaBlocos')
            ->get();

        foreach ($registros as $registro) {

            $blocos = json_decode($registro->listaBlocos, true);

            if (!is_array($blocos)) {
                continue;
            }

            Excel::store(
                new class($blocos) implements WithMultipleSheets {

                    private array $blocos;

                    public function __construct(array $blocos)
                    {
                        $this->blocos = $blocos;
                    }

                    public function sheets(): array
                    {
                        $sheets = [];

                        foreach ($this->blocos as $index => $bloco) {

                            $sheets[] = new class($bloco, $index) implements FromArray, WithTitle {

                                private array $bloco;
                                private int $index;

                                public function __construct(array $bloco, int $index)
                                {
                                    $this->bloco = $bloco;
                                    $this->index = $index;
                                }

                                public function title(): string
                                {
                                    return 'Bloco' . ($this->index + 1);
                                }

                                public function array(): array
                                {
                                    $linhas = [];

                                    foreach ($this->bloco['listaPavimentos'] ?? [] as $pavimento) {

                                        $linha = [];

                                        foreach ($pavimento['listaUnidades'] ?? [] as $unidade) {
                                            $linha[] = $unidade['cat'] ?? '';
                                        }

                                        $linhas[] = $linha;
                                    }

                                    return $linhas;
                                }
                            };
                        }

                        return $sheets;
                    }
                },
                "id{$registro->id}.xlsx",
                'public'
            );
        }

        return response()->json([
            'status' => 'ok',
            'mensagem' => 'Arquivos Excel gerados com sucesso'
        ]);
    }
}
