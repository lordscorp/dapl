<?php

namespace App\DTOs;

readonly class ParametrosCalculoDTO
{
    public function __construct(
        public float $valorM2,
        public float $fatorPlanejamento,
        public float $fatorSocial,
        public ?float $areaTerreno = null,
        public ?float $areaComputavel = null
    ) {}
}
