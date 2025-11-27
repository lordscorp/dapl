<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OutorgaService;

class Outorga extends Controller
{
    public function calcularOutorga(Request $request)
    {
        $validated = $request->validate([
            'at' => 'required|numeric|min:0',
            'ac' => 'required|numeric|min:0.01',
            'v' => 'required|numeric|min:0',
            'fs' => 'required|numeric|min:0|max:1',
            'fp' => 'required|numeric|min:0|max:1.3',
        ]);

        $service = new OutorgaService();
        $resultado = $service->calcularOutorga(
            $validated['at'],
            $validated['ac'],
            $validated['v'],
            $validated['fs'],
            $validated['fp']
        );

        return response()->json(['resultado' => $resultado]);
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
        $service = new OutorgaService();
        $valor = $service->consultarValorM2($ano, $sql, $codlog);

        // Retorna resposta JSON
        return response()->json([
            'ano' => $ano,
            'sql' => $sql,
            'codlog' => $codlog,
            'valor_m2' => $valor
        ]);
    }
}
