<?php

namespace App\Http\Controllers;

use App\Services\BusinessIntelligenceService;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
}
