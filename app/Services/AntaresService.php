<?php

namespace App\Services;

use DateTime;
use App\DTOs\OutorgaDTO;
use App\DTOs\ParametrosCalculoOutorgaDTO;
use App\DTOs\ProcessoDTO;

class AntaresService
{
    public function __construct(
        protected BusinessIntelligenceService $biService,
        protected OutorgaService $outorgaService
    ) {}



    public function obterResumoProcesso(string $processoSei): array
    {
        $dadosProcesso = $this->biService->buscarPorProcesso($processoSei)[0];

        $processoDTO = ProcessoDTO::fromDatabaseArray(
            dadosProcesso: $dadosProcesso,
            setorQuadra: $this->retornarSetorEQuadra($dadosProcesso),
            situacaoTratada: $this->definirSituacao($dadosProcesso),
            codlogTratado: $this->formatarCodlog($dadosProcesso)
        );

        $parametros = $this->montarParametrosBase($dadosProcesso);

        $outorgaDTO = new OutorgaDTO(parametrosDeCalculo: $parametros);

        return [
            'outorga' => $outorgaDTO, 
            'processo' => $processoDTO
        ];
    }

    public function calcularOutorgaAntares(string $processoSei, float $areaTerreno, float $areaComputavel) {
        $dadosProcesso = $this->biService->buscarPorProcesso($processoSei)[0];

        $parametros = $this->montarParametrosBase($dadosProcesso, $areaTerreno, $areaComputavel);

        $valorFinanceiro = $this->outorgaService->calcularOutorga($parametros);

        $outorgaDTO = new OutorgaDTO(
            parametrosDeCalculo: $parametros,
            valorOutorga: $valorFinanceiro // Agora possui o valor final!
        );

        return [
            'outorga' => $outorgaDTO
        ];
    }

    private function montarParametrosBase(
        array $dadosProcesso, 
        ?float $areaTerreno = null, 
        ?float $areaComputavel = null
    ): ParametrosCalculoOutorgaDTO {
        
        $setorQuadra = $this->retornarSetorEQuadra($dadosProcesso);

        return new ParametrosCalculoOutorgaDTO(
            valorM2: $this->consultarValorM2Processo($dadosProcesso),
            fatorPlanejamento: $this->outorgaService->consultarFatorPlanejamento($setorQuadra['setor'], $setorQuadra['quadra']),
            fatorSocial: 1.0,
            areaTerreno: $areaTerreno,
            areaComputavel: $areaComputavel
        );
    }

    function encontrarAno(array $processo): ?string {
        $dataProcesso = !is_null($processo['dtPedidoProtocolo']) ? $processo['dtPedidoProtocolo'] : $processo['dtAutuacaoProcesso'];

        $date = DateTime::createFromFormat("Y-m-d", $dataProcesso);

        if (!$date) {
            throw new \InvalidArgumentException('A data deve ser um string no formato YYYY-mm-dd.');
        }

        return $date->format('Y');
    }

    function consultarValorM2Processo(array $processo): ?float {
        $anoProcesso = $this->encontrarAno($processo);
        $sqlProcesso = $processo['sql_incra'];
        $codlogProcesso = $this->formatarCodlog($processo);

        return $this->outorgaService->consultarValorM2($anoProcesso, $sqlProcesso, $codlogProcesso);
    }

    function formatarCodlog(array $processo): string
    {
        $codlog = $processo['codlog'];

        return preg_replace('/\D/', '', $codlog);
    }

    function retornarSetorEQuadra(array $processo): array
    {
        $sqlFormatado = $this->outorgaService->formatarSQL($processo['sql_incra']);
        $partes = explode('.', $sqlFormatado);

        // Extrai setor e quadra
        $setor = substr($partes[0], 0, 3);
        $quadra = substr($partes[1], 0, 3);

        return ['setor' => $setor, 'quadra' => $quadra];
    }

    function definirSituacao(array $processo): ?string
    {
        return $processo['situacaoProcesso'] ?? $processo['SituacaoAssunto'] ?? null;
    }
}
