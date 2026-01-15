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
        $tSqlIncra = $this->schema . '.' . $this->viewSqlIncra;     // dbo.prata_sql_incra
        $tAssunto  = $this->schema . '.prata_assunto';              // dbo.prata_assunto
        $tInteressado  = $this->schema . '.prata_interessado';      // dbo.prata_interessado

        $digits = preg_replace('/\D+/', '', $sqlIncra ?? '');

        if (strlen($digits) < 10) {
            return [];
        }

        $base10 = substr($digits, 0, 10);
        $setor  = substr($base10, 0, 3);
        $quadra = substr($base10, 3, 3);
        $lote   = substr($base10, 6, 4);

        $query = DB::connection($this->connection)
            ->table($tSqlIncra . ' as sqlincra')
            ->join($tAssunto . ' as passunto', 'passunto.id_prata_assunto', '=', 'sqlincra.id_prata_assunto')
            ->leftJoin($tInteressado . ' as interessado', 'interessado.id_prata_assunto', '=', 'sqlincra.id_prata_assunto')
            ->select([
                'sqlincra.id_prata_sql_incra as id',
                'sqlincra.sql_incra as sql',

                DB::raw("
            STRING_AGG(
                interessado.nomeInteressado + ' (' + interessado.atribuicao + ')',
                '; '
            ) WITHIN GROUP (ORDER BY interessado.atribuicao) AS interessados
        "),

                'passunto.id_prata_assunto',
                'passunto.sistema',
                'passunto.processo',
                'passunto.assunto',
                'passunto.SituacaoAssunto',
                'passunto.dtPedidoProtocolo',
                'passunto.origem_subprefeitura',
                'passunto.aditivo',
            ])
            ->groupBy(
                'sqlincra.id_prata_sql_incra',
                'sqlincra.sql_incra',
                'passunto.id_prata_assunto',
                'passunto.sistema',
                'passunto.processo',
                'passunto.assunto',
                'passunto.SituacaoAssunto',
                'passunto.dtPedidoProtocolo',
                'passunto.origem_subprefeitura',
                'passunto.aditivo',
            );

        if (strlen($digits) >= 11) {
            $digito = substr($digits, 10, 1);
            $formatado = "{$setor}.{$quadra}.{$lote}-{$digito}";

            $query->where('sqlincra.sql_incra', '=', $formatado);
        } else {
            $likePattern = "{$setor}.{$quadra}.{$lote}-%";
            $query->where('sqlincra.sql_incra', 'like', $likePattern);
        }

        $rows = $query->get();

        return $rows->map(fn($r) => (array) $r)->all();
    }

    public function buscarPorSqlIncra_old(string $sqlIncra): array
    {
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
