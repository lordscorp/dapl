<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Arr;

class BusinessIntelligenceService
{
    protected string $connection = 'sqlsrv';
    protected string $schema = 'dbo';
    protected string $viewSqlIncra = 'prata_sql_incra';


    public function buscarPorSqlIncra(string $sqlIncra): array
    {
        // Tabelas totalmente qualificadas (schema.nome)
        $tSqlIncra = $this->schema . '.' . $this->viewSqlIncra;         // dbo.prata_sql_incra
        $tAssunto  = $this->schema . '.prata_assunto';          // dbo.prata_assunto

        $rows = DB::connection($this->connection)
            ->table($tSqlIncra . ' as sqlincra')
            ->join($tAssunto . ' as passunto', 'passunto.id_prata_assunto', '=', 'sqlincra.id_prata_assunto')
            ->where('sqlincra.sql_incra', '=', $sqlIncra)
            ->select([
                'sqlincra.id_prata_sql_incra as id',
                'sqlincra.sql_incra as sql',
                // Seleciona todas as colunas de passunto
                DB::raw('passunto.*'),
            ])
            ->get();

        // Converte Collection -> array de arrays
        return $rows->map(fn($r) => (array) $r)->all();
    }


    public function buscarRaw(string $sqlIncra): ?array
    {
        $sql = 'SELECT TOP 1 * FROM ' . $this->schema . '.' . $this->viewSqlIncra . ' WHERE sql_incra = ?';
        $rows = DB::connection($this->connection)->select($sql, [$sqlIncra]);

        if (empty($rows)) {
            return null;
        }

        return (array) $rows[0];
    }
}
