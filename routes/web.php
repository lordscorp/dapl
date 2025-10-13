<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

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



