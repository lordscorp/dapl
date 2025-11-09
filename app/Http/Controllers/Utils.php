<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Utils extends Controller
{
    public function consultarSubprefeitura(Request $request)
    {
        $sql = $request->query('sql');

        $servApi = env('SERV_API_URL');
        $endpoint = $servApi."subprefeitura/";
        
        
// Validação simples do formato esperado
        if (!preg_match('/^\d{3}\.\d{3}\.\d{4}-\d$/', $sql)) {
            return response()->json(['error' => 'Formato inválido'], 400);
        }

        // Remove o dígito verificador e troca "." por "/"
        $semDigito = substr($sql, 0, strrpos($sql, '-')); // "197.047.0046"
        $formatado = str_replace('.', '/', $semDigito);   // "197/047/0046"

        // Monta a URL do endpoint
        $url = $endpoint . $formatado;

        // Faz a requisição GET
        // $response = Http::get($url);
        $response = Http::withoutVerifying()->get($url);


        // Retorna o conteúdo da resposta
        if ($response->successful()) {
            return response()->json($response->json());
        } else {
            return response()->json(['error' => 'Falha ao consultar o endpoint'], $response->status());
        }

    }

    
public function consultarSubprefeituras(Request $request)
    {
        $sqls = $request->input('sqls'); // Espera um array de SQLs

        if (!is_array($sqls)) {
            return response()->json(['error' => 'O parâmetro sqls deve ser uma lista.'], 400);
        }

        $resultados = [];

        foreach ($sqls as $sql) {
            // Validação simples do formato esperado
            if (!preg_match('/^\d{3}\.\d{3}\.\d{4}-\d$/', $sql)) {
                $resultados[] = [
                    'sql' => $sql,
                    'error' => 'Formato inválido'
                ];
                continue;
            }

            // Remove o dígito verificador e troca "." por "/"
            $semDigito = substr($sql, 0, strrpos($sql, '-')); // "197.047.0046"
            $formatado = str_replace('.', '/', $semDigito);   // "197/047/0046"

            // Monta a URL do endpoint
            $url = 'https://seusite.com/meuendpoint/' . $formatado;

            try {
                $response = Http::withoutVerifying()->get($url);

                if ($response->successful()) {
                    $resultados[] = [
                        'sql' => $sql,
                        'data' => $response->json()
                    ];
                } else {
                    $resultados[] = [
                        'sql' => $sql,
                        'error' => 'Falha ao consultar o endpoint',
                        'status' => $response->status()
                    ];
                }
            } catch (\Exception $e) {
                $resultados[] = [
                    'sql' => $sql,
                    'error' => 'Erro na requisição: ' . $e->getMessage()
                ];
            }
        }

        return response()->json($resultados);
    }

}