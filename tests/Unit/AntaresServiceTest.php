<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AntaresService;
use App\Services\BusinessIntelligenceService;
use App\Services\LogService;
use App\Services\OutorgaService;

class AntaresServiceTest extends TestCase
{
    protected AntaresService $service;
    protected BusinessIntelligenceService $biService;
    protected OutorgaService $outorgaService;

    public function setUp(): void
    {
        parent::setUp();
        $mockLogService = $this->createMock(LogService::class);
        $outorgaService = new OutorgaService($mockLogService);
        $biService = new BusinessIntelligenceService();

        $this->service = new AntaresService($biService, $outorgaService);
    }

    // encontrarAno($arrayProcesso)
    public function test_deve_retornar_ano_autuacao_processo() {
        $arrayProcesso = [
            'dtPedidoProtocolo' => null,
            'dtAutuacaoProcesso' => '2026-01-01'
        ];

        $this->assertEquals('2026', $this->service->encontrarAno($arrayProcesso));
    }

    public function test_deve_retornar_ano_protocolo_processo() {
        $arrayProcesso = [
            'dtPedidoProtocolo' => '2025-01-01',
            'dtAutuacaoProcesso' => '2024-12-31'
        ];

        $this->assertEquals('2025', $this->service->encontrarAno($arrayProcesso));
    }
}
