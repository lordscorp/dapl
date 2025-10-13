<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $nome = $_SESSION['nomeUsuario'] ?? 'Visitante';
    $rf = $_SESSION['IDUsuario'];

    return view('welcome', compact('nome', 'rf'));
});
