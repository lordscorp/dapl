<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BusinessIntelligenceService;
use Illuminate\Http\JsonResponse;

class BusinessIntelligence extends Controller
{
    protected $biService;

    public function __construct(BusinessIntelligenceService $biService)
    {
        $this->biService = $biService;
    }

    /**
     * GET /api/incra?sql_incra=VALOR
     */
    public function consultarSqlIncra(Request $request): JsonResponse
    {
        $sqlIncra = $request->query('sql_incra');

        if (!$sqlIncra || !is_string($sqlIncra)) {
            return response()->json(['erro' => 'Parâmetro sql_incra é obrigatório'], 400);
        }

        try {
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
