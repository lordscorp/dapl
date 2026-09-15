<?php

use App\Http\Controllers\Antares;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ft2\Ft2Processos;
use App\Http\Controllers\Outorga;
use App\Http\Controllers\BusinessIntelligence;
use App\Http\Controllers\Utils;
use App\Http\Controllers\ImpactoZoneamento18177;

Route::get('/impacto-zoneamento-rev18177', [ImpactoZoneamento18177::class, 'consultar']);

Route::get('/ft2/processoAValidar', [Ft2Processos::class, 'processoAValidar']);
Route::get('/ft2/adicionarProcessoAListaNegra', [Ft2Processos::class, 'adicionarProcessoAListaNegra']);
Route::get('/ft2/processoUnidadesAValidar', [Ft2Processos::class, 'processoUnidadesAValidar']);
Route::get('/ft2/dadosDashboard', [Ft2Processos::class, 'dadosDashboard']);
Route::get('/ft2/dadosDashboardFt2', [Ft2Processos::class, 'dadosDashboardFt2']);
Route::get('/ft2/mockDadosDashboard', [Ft2Processos::class, 'mockDadosDashboard']);
Route::post('/ft2/atribuirProcesso', [Ft2Processos::class, 'atribuirProcesso']);
Route::post('/ft2/validarProcesso', [Ft2Processos::class, 'validarProcesso']);
Route::post('/ft2/validarProcessoUnidades', [Ft2Processos::class, 'validarProcessoUnidades']);
Route::get('/ft2/exportarValidados', [Ft2Processos::class, 'exportarValidados']);
Route::get('/ft2/exportarExcelListaBlocos', [Ft2Processos::class, 'exportarExcelListaBlocos']);

Route::get('/consultarSubprefeitura', [Utils::class, 'consultarSubprefeitura']);
Route::post('/consultarSubprefeituras', [Utils::class, 'consultarSubprefeitura']);

// BI
Route::get('/bi/buscarsql', [BusinessIntelligence::class, 'buscarSqlIncra']);
Route::get('/bi/listarFiltros', [BusinessIntelligence::class, 'listarFiltros']);
Route::post('/bi/buscarProcessos', [BusinessIntelligence::class, 'buscarProcessos']);
Route::get('/bi/buscarPorProcesso', [BusinessIntelligence::class, 'buscarPorProcesso']);

// OODC
Route::post('/outorga/calcularOutorga', [Outorga::class, 'calcularOutorga']);
Route::get('/outorga/consultarValorM2', [Outorga::class, 'consultarValorM2']);
Route::get('/outorga/consultarFatorPlanejamento', [Outorga::class, 'consultarFatorPlanejamento']);
Route::get('/outorga/consultarFatorSocial', [Outorga::class, 'consultarFatorSocial']);
Route::get('/outorga/buscarProcessoAD', [Outorga::class, 'buscarProcessoAD']);
Route::get('/outorga/calcularProcessosAD', [Outorga::class, 'calcularProcessosAD']);
Route::get('/outorga/buscarProcessoSISACOE', [Outorga::class, 'buscarProcessoSISACOE']);
Route::get('/outorga/calcularProcessosSISACOE', [Outorga::class, 'calcularProcessosSISACOE']);

//Antares
Route::get('/antares/procurarProcesso', [Antares::class, 'obterResumoProcesso']);
Route::post('/antares/calcularOutorga', [Antares::class, 'calcularOutorgaAntares']);
