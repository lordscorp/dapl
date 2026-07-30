<?php

namespace App\DTOs;

readonly class OutorgaDTO
{
    public function __construct(
        public ParametrosCalculoDTO $parametrosDeCalculo,
        public ?float $valorOutorga = null
    ) {}
}
