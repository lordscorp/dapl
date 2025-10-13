<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;


// Route::get('/', function () {
//     return redirect('login.php'); // ou view('welcome') se quiser usar Blade
// });


Route::get('/', function () {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $nome = $_SESSION['nomeUsuario'] ?? 'Visitante';
    $rf = $_SESSION['IDUsuario'];

    return view('welcome', compact('nome', 'rf'));
});

Route::get('/processoAValidar', function (\Illuminate\Http\Request $request) {
    $rfValidador = $request->query('rfValidador');

    // Busca o primeiro registro com validando = false e validado = false, ordenado por dtEmissao desc
    $registro = DB::table('levantamentohis')
    ->select('processo', 'assunto', 'dtEmissao', 'doc_txt')
    ->whereNull('validando')
    ->whereNull('validado')
    ->whereIn('assunto', [
        'Alvará de Aprovação de Edificação Nova',
        'Alvará de Aprovação e Execução de Edificação Nova'
    ])
    ->where(function ($query) {
        $query->where('doc_txt', 'like', '%H.M.P%')
              ->orWhere('doc_txt', 'like', '%HMP%')
              ->orWhere('doc_txt', 'like', '%H M P%')
              ->orWhere('doc_txt', 'like', '%HIS%')
              ->orWhere('doc_txt', 'like', '%H I S%')
              ->orWhere('doc_txt', 'like', '%H.I.S%');
    })
    ->whereBetween('dtEmissao', ['2014-01-01', '2019-08-13'])
    ->orderByDesc('dtEmissao')
    ->limit(1)
    ->first();


    if (!$registro) {
        return response()->json(['message' => 'Nenhum processo encontrado'], 404);
    }

    return response()->json([
        'objProcesso' => [
            'processo'      => $registro->processo ?? '',
            'assunto'       => $registro->assunto ?? '',
            'dtEmissao'     => $registro->dtEmissao ?? '',
            'docAprovacao'  => $registro->doc_txt ?? '',
            'docConclusao'  => $registro->doc_txt ?? '',
            'uniCatUso'     => [], // sempre vazio
        ]
    ]);
});
