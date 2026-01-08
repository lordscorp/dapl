<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OutorgaService;

class Outorga extends Controller
{
    protected $outorgaService;


    public function __construct(OutorgaService $outorgaService)
    {
        $this->outorgaService = $outorgaService;
    }


    public function calcularOutorga(Request $request)
    {
        $validated = $request->validate([
            'at' => 'required|numeric|min:0',
            'ac' => 'required|numeric|min:0.01',
            'v' => 'required|numeric|min:0',
            'fs' => 'required|numeric|min:0|max:1',
            'fp' => 'required|numeric|min:0|max:1.3',
        ]);

        // $service = new OutorgaService();
        // $resultado = $service->calcularOutorga(
        $resultado = $this->outorgaService->calcularOutorga(
            $validated['at'],
            $validated['ac'],
            $validated['v'],
            $validated['fs'],
            $validated['fp']
        );

        return response()->json(['resultado' => $resultado]);
    }


    /**
     * Endpoint para buscar processo AD.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarProcessoAD(Request $request)
    {
        // $processo = $request->input('processo');
        $processo = urldecode($request->query('processo'));
        $fs = $request->input('fs');

        if (!$processo) {
            return response()->json(['error' => 'Parâmetro "processo" é obrigatório.'], 400);
        }

        $resultado = $this->outorgaService->buscarProcessoAD($processo, $fs);

        if (!$resultado) {
            return response()->json(['message' => 'Processo não encontrado.'], 404);
        }

        return response()->json($resultado);
    }

    /**
     * Endpoint para buscar processo SISACOE.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function buscarProcessoSISACOE(Request $request)
    {
        // $processo = $request->input('processo');
        $processo = urldecode($request->query('processo'));
        $fs = $request->input('fs');

        if (!$processo) {
            return response()->json(['error' => 'Parâmetro "processo" é obrigatório.'], 400);
        }

        $resultado = $this->outorgaService->buscarProcessoSISACOE($processo, $fs);

        if (!$resultado) {
            return response()->json(['message' => 'Processo não encontrado.'], 404);
        }

        return response()->json($resultado);
    }

    public function calcularProcessosAD(Request $request)
    {
        $paginacao = $request->input('paginacao') ? $request->input('paginacao') : 1;
        $fs = $request->input('fs') ? $request->input('fs') : 1;
        
        // echo "\r\nCALCULAR PROCESSO - paginacao: " . $paginacao . " fs: " . $fs;

        $resultado = $this->outorgaService->calcularProcessosAD($paginacao, $fs);

        return response()->json($resultado);

    }

    public function calcularProcessosSISACOE(Request $request)
    {
        $paginacao = $request->input('paginacao') ? $request->input('paginacao') : 1;
        $fs = $request->input('fs') ? $request->input('fs') : 1;
        
        $resultado = $this->outorgaService->calcularProcessosSISACOE($paginacao, $fs);

        return response()->json($resultado);

    }


    public function consultarFatorPlanejamento(Request $request)
    {
        $setor = $request->input('setor');
        $quadra = $request->input('quadra');

        $fp = $this->outorgaService->consultarFatorPlanejamento($setor, $quadra);

        return response()->json([
            'fp' => $fp
        ]);
    }


    public function consultarValorM2(Request $request)
    {
        // Valida parâmetros obrigatórios
        $request->validate([
            'ano' => 'required|integer',
            'sql' => 'required|string',
            'codlog' => 'required|string'
        ]);

        $ano = (int) $request->get('ano');
        $sql = $request->get('sql');
        $codlog = $request->get('codlog');

        // Chama a service
        // $service = new OutorgaService();
        // $valor = $service->consultarValorM2($ano, $sql, $codlog);
        $valor = $this->outorgaService->consultarValorM2($ano, $sql, $codlog);

        // Retorna resposta JSON
        return response()->json([
            'ano' => $ano,
            'sql' => $sql,
            'codlog' => $codlog,
            'valor_m2' => $valor
        ]);
    }
}
