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
}
