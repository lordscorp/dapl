<?php

namespace App\DTOs;

class ParametrosCalculoOutorgaDTO
{
    public function __construct(
        /** @var float V - valor do m² do terreno constante do Cadastro de Valor de Terreno (Quadro 14 anexo) */
        public readonly float $valorM2,

        /** @var float Fp - fator de planejamento entre 0 e 1,3 (Quadro 6 anexo) */
        public readonly float $fatorPlanejamento,

        /** @var float Fs - fator de interesse social, entre 0 e 1 (Quadro 5 anexo) */
        public readonly float $fatorSocial,

        /** @var float|null At - área de terreno em m² */
        public ?float $areaTerreno = null,

        /** @var float|null Ac - área construída computável total pretendida no empreendimento em m² */
        public ?float $areaComputavel = null
    ) {}

    /**
     * Valida as invariantes necessárias para a realização do cálculo de outorga.
     *
     * @throws \InvalidArgumentException
     */
    public function validarParaCalculo(): void
    {
        if (is_null($this->areaTerreno) || is_null($this->areaComputavel)) {
            throw new \InvalidArgumentException('As áreas devem ser informadas para o cálculo.');
        }

        if ($this->areaTerreno <= 0 || $this->areaComputavel <= 0) {
            throw new \InvalidArgumentException('Áreas devem ser maiores que zero.');
        }
    }
}
