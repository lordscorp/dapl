<?php

namespace App\Services;

use DateTime;
use App\DTOs\OutorgaDTO;
use App\DTOs\ParametrosCalculoDTO;
use App\DTOs\ProcessoDTO;

class AntaresService
{
    public function __construct(
        protected BusinessIntelligenceService $biService,
        protected OutorgaService $outorgaService
    ) {}

    public function processarRequisicaoAntares(string $processoSei): array
    {
        $dadosProcesso = $this->biService->buscarPorProcesso($processoSei)[0];

        $setorQuadra = $this->retornarSetorEQuadra(($dadosProcesso));
        
        $processo = new ProcessoDTO(
            processoSei: $dadosProcesso['processo'],
            dataAutuacao: $dadosProcesso['dtAutuacaoProcesso'],
            situacao: $this->definirSituacao($dadosProcesso),
            sistema: $dadosProcesso['sistema'],
            protocolo: $dadosProcesso['protocolo'],
            dataProtocolo: $dadosProcesso['dtPedidoProtocolo'],
            setor: $setorQuadra['setor'],
            quadra: $setorQuadra['quadra'],
            codlog: $this->formatarCodlog($dadosProcesso)         
        );

        $parametrosCalculo = new ParametrosCalculoDTO(
            valorM2: $this->consultarValorM2Processo($dadosProcesso),
            fatorPlanejamento: $this->outorgaService->consultarFatorPlanejamento($processo->setor, $processo->quadra),
            fatorSocial: 1.0
        );

        $outorga = new OutorgaDTO (
            parametrosDeCalculo: $parametrosCalculo
        );
                
        return Array('processo' => $processo, 'outorga' => $outorga);
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
