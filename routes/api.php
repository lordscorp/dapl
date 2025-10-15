<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Processos;

Route::get('/processoAValidar', [Processos::class, 'processoAValidar']);
Route::post('/validarProcesso', [Processos::class, 'validarProcesso']);
