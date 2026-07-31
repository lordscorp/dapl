<?php

namespace App\DTOs;

readonly class ProcessoDTO
{
    public function __construct(
        public string $processoSei,
        public string $dataAutuacao,
        public string $situacao,
        public string $sistema,
        public string $protocolo,
        public string $dataProtocolo,
        public string $setor,
        public string $quadra,
        public string $codlog
    ) {}

    public static function fromDatabaseArray(
        array $dadosProcesso,
        array $setorQuadra,
        string $situacaoTratada,
        string $codlogTratado
    ): self {
        return new self(
            processoSei: $dadosProcesso['processo'],
            dataAutuacao: $dadosProcesso['dtAutuacaoProcesso'],
            situacao: $situacaoTratada,
            sistema: $dadosProcesso['sistema'],
            protocolo: $dadosProcesso['protocolo'],
            dataProtocolo: $dadosProcesso['dtPedidoProtocolo'],
            setor: $setorQuadra['setor'],
            quadra: $setorQuadra['quadra'],
            codlog: $codlogTratado
        );
    }
}
