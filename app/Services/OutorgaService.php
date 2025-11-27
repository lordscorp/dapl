<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class OutorgaService
{
    /**
     * OODC - Outorga Onerosa do Direito de Construir
     * - C = Contrapartida financeira por m² de potencial construtivo adicional.
     * @param float $at -   At - área de terreno em m²;
     * @param float $ac -   Ac - área construída computável total pretendida no empreendimento em m²;
     * @param float $v -   V - valor do m² do terreno constante do Cadastro de Valor de Terreno para fins de Outorga Onerosa, conforme Quadro 14 anexo;
     * @param float $fs -   Fs - fator de interesse social, entre 0 (zero) e 1 (um), conforme Quadro 5 anexo;
     * @param float $fp -   Fp - fator de planejamento entre 0 (zero) e 1,3 (um e três décimos), conforme Quadro 6 anexo;
     */
    public function calcularOutorga(float $at, float $ac, float $v, float $fs, float $fp): float
    {


        if ($ac <= 0 || $at <= 0) {
            throw new \InvalidArgumentException('Áreas devem ser maiores que zero.');
        }

        if ($ac < $at) {
            throw new \InvalidArgumentException('Area computavel menor que area total gera OODC invalida.');
        }

        $c = ($at / $ac) * $v * $fs * $fp;

        $total = $c * ($ac - $at);

        return round($total, 2);
    }

    /**
     * Formata código SQL (Setor, Quadra, Lote)
     * @param string $sql Código SQL (Setor, Quadra, Lote) com 11 dígitos
     */
    public function formatarSQL($sql)
    {
        // Remove espaços e caracteres não numéricos
        $numeros = preg_replace('/\D/', '', $sql);

        // Verifica se contém apenas números e tem tamanho esperado (11 dígitos)
        if (ctype_digit($numeros) && strlen($numeros) === 11) {
            // Formata no padrão ###.###.####-#
            return substr($numeros, 0, 3) . '.' .
                substr($numeros, 3, 3) . '.' .
                substr($numeros, 6, 4) . '-' .
                substr($numeros, 10, 1);
        }

        // Se não for válido, retorna original
        return $sql;
    }

    function consultarValorM2(int $ano, string $sql, string $codlog): ?float
    {
        // Reatribui usando a função já existente
        $sqlFormatado = $this->formatarSQL($sql);

        // Quebra pelo ponto
        $partes = explode('.', $sqlFormatado);

        // Valida se temos pelo menos 3 partes
        if (count($partes) < 3) {
            return null; // ou lançar exceção
        }

        // Extrai setor, quadra e lote
        $setor = substr($partes[0], 0, 3);
        $quadra = substr($partes[1], 0, 3);
        $lote   = substr($partes[2], 0, 3); // caso precise no futuro

        // Monta nome da tabela dinamicamente
        // $tabela = 'quadro14_vm2_' . intval($ano);
        $tabela = 'quadro14_vm2_2024';

        // Consulta usando Query Builder
        $resultado = DB::table($tabela)
            ->where('setor', $setor)
            ->where('quadra', $quadra)
            ->where('codlog', $codlog)
            ->value('vm2'); // retorna apenas o valor da coluna

        return $resultado !== null ? (float)$resultado : null;
    }
}
