<?php

namespace App\DTOs;

readonly class OutorgaDTO
{
    public function __construct(
        public ParametrosCalculoOutorgaDTO $parametrosDeCalculo,
        public ?float $valorOutorga = null
    ) {}
}
