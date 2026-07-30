<?php

namespace App\DTOs;

readonly class ProcessoDTO
{
    public function __construct(
        public string $processoSei,
        public string $dataAutuacao,
        public string $protocolo,
        public string $dataProtocolo,
        public string $sqlIncra,
        public string $codlog
    ) {}
}
