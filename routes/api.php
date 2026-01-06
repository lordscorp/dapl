<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Processos;
use App\Http\Controllers\Outorga;
use App\Http\Controllers\BusinessIntelligence;
use App\Http\Controllers\Utils;

Route::get('/processoAValidar', [Processos::class, 'processoAValidar']);
Route::get('/dadosDashboard', [Processos::class, 'dadosDashboard']);
Route::get('/mockDadosDashboard', [Processos::class, 'mockDadosDashboard']);
Route::post('/validarProcesso', [Processos::class, 'validarProcesso']);
Route::get('/exportarValidados', [Processos::class, 'exportarValidados']);
Route::get('/consultarSubprefeitura', [Utils::class, 'consultarSubprefeitura']);
Route::post('/consultarSubprefeituras', [Utils::class, 'consultarSubprefeitura']);

// BI
Route::get('/bi/sql', [BusinessIntelligence::class, 'consultarSqlIncra']);

Route::post('/outorga/calcularOutorga', [Outorga::class, 'calcularOutorga']);
Route::get('/outorga/consultarValorM2', [Outorga::class, 'consultarValorM2']);
Route::get('/outorga/buscarProcessoAD', [Outorga::class, 'buscarProcessoAD']);
Route::get('/outorga/consultarFatorPlanejamento', [Outorga::class, 'consultarFatorPlanejamento']);
Route::get('/outorga/calcularProcessosAD', [Outorga::class, 'calcularProcessosAD']);
