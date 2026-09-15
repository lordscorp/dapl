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

        return DB::table('impacto_zoneamento_rev18177')
            ->where('sql_truncado', 'like', $sqlTruncado . '%')
            ->get();
    }
}