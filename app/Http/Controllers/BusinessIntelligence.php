<?php

namespace App\Http\Controllers;

use App\Services\BusinessIntelligenceService;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Exports\ProcessosBiExport;
use Maatwebsite\Excel\Facades\Excel;

class BusinessIntelligence extends Controller
{
    protected $biService;
    protected $logService;

    public function __construct(BusinessIntelligenceService $biService, LogService $logService)
    {
        $this->biService = $biService;
        $this->logService = $logService;
    }

    /**
     * GET /api/bi/buscarsql?sql_incra=VALOR
     */
    public function buscarSqlIncra(Request $request): JsonResponse
    {
        $sqlIncra = $request->query('sql_incra');

        if (!$sqlIncra || !is_string($sqlIncra)) {
            return response()->json(['erro' => 'Parâmetro sql_incra é obrigatório'], 400);
        }

        try {
            $this->logService->registrarDaSessao("Busca SQL", $sqlIncra);

            $resultado = $this->biService->buscarPorSqlIncra($sqlIncra);
        } catch (\Throwable $e) {
            // Evita vazar detalhes de erro
            return response()->json(['erro' => 'Falha ao consultar a base SQL Server'], 500);
        }

        if (!$resultado) {
            return response()->json(['mensagem' => 'Não encontrado'], 404);
        }

        return response()->json($resultado, 200);
    }

    /**
     * POST /api/bi/buscarProcessos
     */

    public function buscarProcessos(Request $request)
    {
        set_time_limit(10);

        $dados = $request->validate([
            'dataInicio'        => 'required|date',
            'dataFim'           => 'nullable|date',
            'assuntos'          => 'nullable|array',
            'situacoes'         => 'nullable|array',
            'distritos'         => 'nullable|array',
            'subprefeituras'    => 'nullable|array',
            'gerarXlsx'         => 'nullable|boolean',
        ]);

        $parametrosUsados = collect($dados)->toJson(JSON_PRETTY_PRINT);

        $this->logService->registrarDaSessao("Busca Avançada", $parametrosUsados);

        if ($request->boolean('gerarXlsx')) {
            $query = $this->biService->buscarProcessos($dados, true);

            return Excel::download(
                new ProcessosBiExport($query),
                'processos.xlsx'
            );
        }

        $resultado = $this->biService->buscarProcessos($dados);
        return response()->json($resultado);
    }
    
    /**
     * GET /api/bi/listarFiltros
     */

    public function listarFiltros()
    {
        return response()->json(
            $this->biService->listarFiltros()
        );
    }

    /**
     * GET /api/bi/buscarPorProcesso
     */

    public function buscarPorProcesso(Request $request): JsonResponse
    {
        $processoSei= $request->query('processo_sei');

        if (!$processoSei || !is_string($processoSei)) {
            return response()->json(['erro' => 'Parâmetro processo_sei é obrigatório'], 400);
        }

        try {
            $this->logService->registrarDaSessao("Busca Por Processo", $processoSei);

            $resultado = $this->biService->buscarPorProcesso($processoSei);
        } catch (\Throwable $e) {
            // Evita vazar detalhes de erro
            return response()->json(['erro' => 'Falha ao consultar a base SQL Server'], 500);
        }

        if (!$resultado) {
            return response()->json(['mensagem' => 'Não encontrado'], 404);
        }

        return response()->json($resultado, 200);
    }
}
