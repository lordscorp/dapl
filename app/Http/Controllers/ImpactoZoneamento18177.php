<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ImpactoZoneamento18177Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImpactoZoneamento18177 extends Controller
{
    public function __construct(
        protected ImpactoZoneamento18177Service $service
    ) {
    }

    /**
     * Consulta impacto de zoneamento pelo SQL.
     */
    public function consultar(Request $request): JsonResponse
    {
        $request->validate([
            'sql' => ['required', 'string'],
        ]);

        $resultado = $this->service->buscarPorSql($request->input('sql'));

        // if (!$resultado) {
        if ($resultado->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum registro encontrado para o SQL informado.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $resultado,
        ]);
    }
}
