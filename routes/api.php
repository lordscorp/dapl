<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Processos;
use App\Http\Controllers\Outorga;
use App\Http\Controllers\BusinessIntelligence;
use App\Http\Controllers\Utils;

Route::get('/processoAValidar', [Processos::class, 'processoAValidar']);
Route::get('/adicionarProcessoAListaNegra', [Processos::class, 'adicionarProcessoAListaNegra']);
Route::get('/processoUnidadesAValidar', [Processos::class, 'processoUnidadesAValidar']);
Route::get('/dadosDashboard', [Processos::class, 'dadosDashboard']);
Route::get('/dadosDashboardFt2', [Processos::class, 'dadosDashboardFt2']);
Route::get('/mockDadosDashboard', [Processos::class, 'mockDadosDashboard']);
Route::post('/atribuirProcesso', [Processos::class, 'atribuirProcesso']);
Route::post('/validarProcesso', [Processos::class, 'validarProcesso']);
Route::post('/validarProcessoUnidades', [Processos::class, 'validarProcessoUnidades']);
Route::get('/exportarValidados', [Processos::class, 'exportarValidados']);
Route::get('/exportarExcelListaBlocos', [Processos::class, 'exportarExcelListaBlocos']);
Route::get('/consultarSubprefeitura', [Utils::class, 'consultarSubprefeitura']);
Route::post('/consultarSubprefeituras', [Utils::class, 'consultarSubprefeitura']);

// BI
Route::get('/bi/buscarsql', [BusinessIntelligence::class, 'buscarSqlIncra']);
Route::get('/bi/listarFiltros', [BusinessIntelligence::class, 'listarFiltros']);
Route::post('/bi/buscarProcessos', [BusinessIntelligence::class, 'buscarProcessos']);

// OODC
Route::post('/outorga/calcularOutorga', [Outorga::class, 'calcularOutorga']);
Route::get('/outorga/consultarValorM2', [Outorga::class, 'consultarValorM2']);
Route::get('/outorga/consultarFatorPlanejamento', [Outorga::class, 'consultarFatorPlanejamento']);
Route::get('/outorga/buscarProcessoAD', [Outorga::class, 'buscarProcessoAD']);
Route::get('/outorga/calcularProcessosAD', [Outorga::class, 'calcularProcessosAD']);
Route::get('/outorga/buscarProcessoSISACOE', [Outorga::class, 'buscarProcessoSISACOE']);
Route::get('/outorga/calcularProcessosSISACOE', [Outorga::class, 'calcularProcessosSISACOE']);
