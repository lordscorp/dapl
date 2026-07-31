<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Services\LogService;
use App\Services\AntaresService;
use App\Services\BusinessIntelligenceService;
use App\Services\OutorgaService;
use App\DTOs\ProcessoDTO;
use App\DTOs\ParametrosCalculoOutorgaDTO;
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

        $parametrosEsperados = new ParametrosCalculoOutorgaDTO(
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
        
        $resultado = $this->service->obterResumoProcesso('0123.2024/0121323-4');

        $this->assertEquals($arrayEsperado, $resultado);
    }

    public function test_deve_retornar_calculo_da_outorga_com_parametros() 
    {
        // 1. ARRANGE (Preparamos o gabarito esperado instanciando os DTOs)
        $parametrosEsperados = new ParametrosCalculoOutorgaDTO( // Ajuste o nome da classe se usou ParametrosCalculoOutorgaDTO
            valorM2: 3475.20,
            fatorPlanejamento: 0.5, // Certifique-se de que o mock do banco no setUp() devolve 0.5 para esse Setor/Quadra
            fatorSocial: 1.0,
            areaTerreno: 2500.0,
            areaComputavel: 7000.0
        );

        $outorgaEsperada = new OutorgaDTO(
            parametrosDeCalculo: $parametrosEsperados,
            valorOutorga: 2792571.43 // O valor exato que a matemática deve resultar
        );

        // O array esperado é estruturado exatamente como o retorno do AntaresService
        $arrayEsperado = [
            'outorga' => $outorgaEsperada
        ];

        // 2. ACT (Agimos chamando o Service com os dados simulados do POST)
        // Passamos a string do processo e as duas áreas (Terreno e Computável)
        $resultado = $this->service->calcularOutorgaAntares(
            '0123.2024/0121323-4', 
            2500.0, 
            7000.0
        );

        // 3. ASSERT (Verificamos se o que o Service gerou é idêntico ao nosso gabarito)
        $this->assertEquals($arrayEsperado, $resultado);
    }
}
