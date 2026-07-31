<?php

namespace App\Http\Controllers;

use App\Services\AntaresService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class Antares extends Controller
{
    protected AntaresService $antaresService;

    public function __construct(AntaresService $antaresService)
    {
        $this->antaresService = $antaresService;
    }

    /**
     * GET: /api/antares/procurarProcesso
     */
    public function obterResumoProcesso(Request $request): JsonResponse 
    {
        $validated = $request->validate([
            // Exige que exista, seja string, e obedeça ao formato 0000.0000/0000000-0
            'processo_sei' => [
                'required', 
                'string', 
                'regex:/^\d{4}\.\d{4}\/\d{7}-\d$/' 
            ]
        ]);

        $resultado = $this->antaresService->obterResumoProcesso($validated['processo_sei']);

        return response()->json($resultado);
    }
    
    /**
     * POST: /api/antares/calcularOutorga
     */
    public function calcularOutorgaAntares(Request $request): JsonResponse 
    {
        $validated = $request->validate([
            'processoSei' => [
                'required', 
                'string', 
                'regex:/^\d{4}\.\d{4}\/\d{7}-\d$/'
            ],
            'areaTerreno' => 'required|numeric|gt:0',
            'areaComputavel' => 'required|numeric|gt:0'
        ]);

        $resultado = $this->antaresService->calcularOutorgaAntares(
            $validated['processoSei'], 
            (float) $validated['areaTerreno'], 
            (float) $validated['areaComputavel']
        );

        return response()->json($resultado);
    }
}
