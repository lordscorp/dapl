<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Processos;

Route::get('/processoAValidar', [Processos::class, 'processoAValidar']);
Route::get('/dadosDashboard', [Processos::class, 'dadosDashboard']);
Route::get('/mockDadosDashboard', [Processos::class, 'mockDadosDashboard']);
Route::post('/validarProcesso', [Processos::class, 'validarProcesso']);
