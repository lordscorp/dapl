<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Services\LogService;
use App\Services\AntaresService;
use App\Services\BusinessIntelligenceService;
use App\Services\OutorgaService;
use Tests\Traits\PreparaDadosBi;
use Tests\Traits\PreparaDadosOutorga;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AntaresIntegrationTest extends TestCase
{
    use PreparaDadosBi, PreparaDadosOutorga, RefreshDatabase;

    protected AntaresService $service;

    public function setUp(): void
    {
        parent::setUp();
        
        $mockLogService = $this->createMock(LogService::class);
        $outorgaService = new OutorgaService($mockLogService);
        $biService = new BusinessIntelligenceService();

        $this->service = new AntaresService($biService, $outorgaService);

        $this->popularBancoBi();
        $this->popularBancoOutorga();
    }

    public function test_deve_retornar_valor_m2_com_dados_de_processo() {
        $arrayProcesso = [
            'dtPedidoProtocolo' => '2024-02-02',
            'sqlIncra' => '055.033.0012-1',
            'codlog' => '151048'
        ];

        $resultado = $this->service->consultarValorM2Processo($arrayProcesso);
        
        $this->assertEquals($resultado, 3475.20);
    }

    public function test_deve_retornar_codlog_apenas_numeros() {
        $arrayProcesso = ['codlog' => '123.45-6'];

        $resultado = $this->service->formatarCodlog($arrayProcesso);

        $this->assertEquals('123456', $resultado);
    }

    public function test_deve_retornar_array_com_dados_de_processo() {
        $arrayEsperado = [
            'processo' => [
                'processoSei' => '0123.2024/0121323-4',
                'dataAutuacao' => '2024-02-28',
                'protocolo' => '32109-24-SP-ALV',
                'dataProtocolo' => '2024-02-28',
                'sqlIncra' => '055.033.0012-1',
                'codlog' => '151048'
            ],
            'outorga' => [
                'parametrosDeCalculo' => [
                    'valorM2' =>  3475.20,
                    'fatorPlanejamento' => 0.8,
                    'fatorSocial' => 1
                ]
            ]
        ];
        
        $resultado = $this->service->processarRequisicaoAntares('0123.2024/0121323-4');

        $this->assertEquals($arrayEsperado, $resultado);
    }

    public function test_deve_retornar_calculo_da_outorga_com_parametros() {
        $arrayEsperado = [
            'outorga' => [
                'parametrosDeCalculo' => [
                    'valorM2' =>  3475.20,
                    'fatorPlanejamento' => 0.8,
                    'fatorSocial' => 1,
                    'areaTerreno' => 2500.00,
                    'areaComputavel' => 7000.00
                ],
                'valorOutorga' => 4468114.29
            ]
        ];

        $resultado = $this->service->calcularOutorgaAntares(2500, 7000, 3475.20, 1, 0.8);

        $this->assertEquals($arrayEsperado, $resultado);
    }
}
