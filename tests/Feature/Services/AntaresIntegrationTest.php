<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Services\LogService;
use App\Services\AntaresService;
use App\Services\BusinessIntelligenceService;
use App\Services\OutorgaService;
use App\DTOs\ProcessoDTO;
use App\DTOs\ParametrosCalculoDTO;
use App\DTOs\OutorgaDTO;
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
            'sql_incra' => '135.241.0012-1',
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

    public function test_deve_retornar_setor_e_quadra_com_sql_incra() {
        $arrayProcesso = ['sql_incra' => '135.241.0012-1'];

        $arrayEsperado = ['setor' => '135', 'quadra' => '241'];

        $resultado = $this->service->retornarSetorEQuadra($arrayProcesso);

        $this->assertEquals($resultado, $arrayEsperado);
    }

    public function test_deve_retornar_array_com_dados_de_processo() {
        $processoEsperado = new ProcessoDTO(
            processoSei: '0123.2024/0121323-4',
            dataAutuacao: '2024-02-28',
            situacao: 'Em Andamento',
            sistema: 'Aprovanet',
            protocolo: '32109-24-SP-ALV',
            dataProtocolo: '2024-02-28',
            setor: '135',
            quadra: '241',
            codlog: '151048'
        );

        $parametrosEsperados = new ParametrosCalculoDTO(
            valorM2: 3475.20,
            fatorPlanejamento: 0.5,
            fatorSocial: 1.0
        );

        $outorgaEsperada = new OutorgaDTO(
            parametrosDeCalculo: $parametrosEsperados
        );

        $arrayEsperado = [
            'processo' => $processoEsperado,
            'outorga'  => $outorgaEsperada
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
