<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ImpactoZoneamento18177Service
{
    /**
     * Consulta impacto de zoneamento pelo SQL (Setor/Quadra/Lote).
     *
     * Remove espaços, pontos e traços, considera apenas os 10 primeiros
     * dígitos e pesquisa na coluna sql_truncado ignorando o dígito verificador.
     *
     * Exemplo:
     *  123.456.789-0 => 1234567890
     *
     * @param string $sql
     * @return object|null
     */
    public function buscarPorSql(string $sql)
    {
        // Remove espaços, pontos e traços
        $sqlSanitizado = str_replace([' ', '.', '-'], '', $sql);

        // Considera apenas os 10 primeiros caracteres
        $sqlTruncado = substr($sqlSanitizado, 0, 10);

        return DB::table('impacto_zoneamento_rev18177_v1')
            ->where('sql_truncado', 'like', $sqlTruncado . '%')
            ->get();
    }

    /**
     * Consulta impacto de zoneamento por Setor/Quadra.
     *
     * Exemplo:
     * 123456
     * 123.456
     *
     * @param string $sq
     * @return \Illuminate\Support\Collection
     */
    public function buscarPorSQ(string $sq)
    {
        // Remove espaços, pontos e traços
        $sqSanitizado = str_replace([' ', '.', '-'], '', $sq);

        // Considera apenas os 6 primeiros caracteres (Setor + Quadra)
        $sqTruncado = substr($sqSanitizado, 0, 6);

        return DB::table('impacto_zoneamento_rev18177_v1')
            ->where('sql_truncado', 'like', $sqTruncado . '%')
            ->get();
    }
}