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

    if ($nome === 'Visitante') {
        return view('tutorial');
    }

    $rf = $_SESSION['IDUsuario'];

    return view('dashboard', compact('nome', 'rf'));
})->middleware('checarrf');

Route::get('/validacao', function () {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $nome = $_SESSION['nomeUsuario'] ?? 'Visitante';
    $rf = $_SESSION['IDUsuario'];

    return view('validacao', compact('nome', 'rf'));
})->middleware('checarrf');

Route::get('/businessintelligence', function () {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $nome = $_SESSION['nomeUsuario'] ?? 'Visitante';
    $rf = $_SESSION['IDUsuario'];

    return view('businessintelligence', compact('nome', 'rf'));
})
->middleware('checarrf')
->name('businessintelligence');

Route::get('/outorga', function () {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $nome = $_SESSION['nomeUsuario'] ?? 'Visitante';
    $rf = $_SESSION['IDUsuario'];

    return view('outorga', compact('nome', 'rf'));
})->middleware('checarrf');

Route::get('/tutorial', function () {
    return view('tutorial');
});


