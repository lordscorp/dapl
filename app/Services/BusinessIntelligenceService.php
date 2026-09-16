<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
// use Illuminate\Support\Arr;

class BusinessIntelligenceService
{
    protected string $connection;
    protected string $schema;
    protected string $viewSqlIncra = 'prata_sql_incra';
    protected string $viewAssuntos = 'prata_assunto';
    protected string $viewInteressados = 'prata_interessado';
    protected string $viewProcessos = 'prata_processo';
    protected string $viewEnderecos = 'prata_endereco';


    public function __construct()
    {
        $this->connection = env('SQLSRV_CONNECTION', 'sqlsrv');
        $this->schema = env('DB_SQLSRV_SCHEMA', 'dbo');
    }

    protected function prefixoSchema(): string
    {
        return $this->schema ? $this->schema . '.' : '';
    }

    private function getInteressadosRaw()
    {
        // Se estiver rodando no banco de testes (SQLite), usa a sintaxe compatível
        if ($this->connection === 'sqlite') {
            return DB::raw("GROUP_CONCAT(interessado.nomeInteressado || ' (' || interessado.atribuicao || ')', '; ') AS interessados");
        }

        // Caso contrário, usa a sintaxe oficial do SQL Server
        return DB::raw("
            STRING_AGG(
                interessado.nomeInteressado + ' (' + interessado.atribuicao + ')',
                '; '
            ) WITHIN GROUP (ORDER BY interessado.atribuicao) AS interessados
        ");
    }

    public function buscarPorProcesso(string $processoSei)
    {
        $tProcesso = $this->prefixoSchema() . $this->viewProcessos;         // dbo.prata_processo
        $tSqlIncra = $this->prefixoSchema() . $this->viewSqlIncra;          // dbo.prata_sql_incra
        $tAssunto = $this->prefixoSchema() . $this->viewAssuntos;           // dbo.prata_assunto
        $tEndereco = $this->prefixoSchema() . $this->viewEnderecos;         // dbo.prata_endereco


        $query = DB::connection($this->connection)
            ->table($tProcesso . ' AS proc')
            ->select([
                'proc.sistema',
                'proc.processo',
                'proc.dtAutuacaoProcesso',
                'proc.situacaoProcesso',
                'proc.tipoprocesso',

                'assunto.protocolo',
                'assunto.dtPedidoProtocolo',
                'assunto.SituacaoAssunto'
            ])
            ->addSelect([
                'sql_incra' => DB::connection($this->connection)
                    ->table($tSqlIncra . ' AS sqlincra')
                    ->select(DB::raw('MAX(sql_incra)'))
                    ->whereColumn('sqlincra.processo', 'proc.processo')
            ])
            ->addSelect([
                'codlog' => DB::connection($this->connection)
                    ->table($tEndereco . ' AS endereco')
                    ->select(DB::raw('MAX(codlog)'))
                    ->whereColumn('endereco.processo', 'proc.processo')
            ])
            ->leftJoin($tAssunto . ' AS assunto', function ($join) {
                $join->on('assunto.processo', '=', 'proc.processo')
                    ->where('assunto.assunto', 'LIKE', 'Alvará de Aprovação de Edificação Nova%');
            })
            ->where('proc.processo', $processoSei);


        $rows = $query->get();

        return $rows->map(fn($r) => (array) $r)->all();
    }

    public function buscarPorSqlIncra(string $sqlIncra): array
    {
        $tSqlIncra = $this->prefixoSchema() . $this->viewSqlIncra;              // dbo.prata_sql_incra
        $tAssunto  = $this->prefixoSchema() . $this->viewAssuntos;              // dbo.prata_assunto
        $tInteressado  = $this->prefixoSchema() . $this->viewInteressados;      // dbo.prata_interessado

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

                $this->getInteressadosRaw(),

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
        $tSqlIncra = $this->prefixoSchema() . $this->viewSqlIncra;          // dbo.prata_sql_incra
        $tAssunto  = $this->prefixoSchema() . $this->viewAssuntos;          // dbo.prata_assunto

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

    public function buscarProcessosQuery(array $filtros)
    {
        // ->table("{$this->schema}.{$this->viewAssuntos}")
        $tSqlIncra = $this->prefixoSchema() . $this->viewSqlIncra;              // dbo.prata_sql_incra
        $tAssunto  = $this->prefixoSchema() . $this->viewAssuntos;              // dbo.prata_assunto
        $tInteressado  = $this->prefixoSchema() . $this->viewInteressados;      // dbo.prata_interessado

        $query = DB::connection($this->connection)
            ->table($tSqlIncra . ' as sqlincra')
            ->join($tAssunto . ' as passunto', 'passunto.id_prata_assunto', '=', 'sqlincra.id_prata_assunto')
            ->leftJoin($tInteressado . ' as interessado', 'interessado.id_prata_assunto', '=', 'sqlincra.id_prata_assunto')
            ->select([
                'sqlincra.id_prata_sql_incra as id',
                'sqlincra.sql_incra as sql',

                $this->getInteressadosRaw(),

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
            )
            ->orderBy('id');

        // $query = DB::connection($this->connection)
        //     ->table("{$this->schema}.{$this->viewAssuntos}")
        //     ->whereDate('dtPedidoProtocolo', '>=', $filtros['dataInicio'])
        //     ->orderBy('dtPedidoProtocolo')
        //     ->orderBy('id');

        if (!empty($filtros['dataFim'])) {
            $query->whereDate('dtPedidoProtocolo', '<=', $filtros['dataFim']);
        }

        if (!empty($filtros['assuntos'])) {
            $query->whereIn('assunto', $filtros['assuntos']);
        }

        if (!empty($filtros['situacoes'])) {
            $query->whereIn('SituacaoAssunto', $filtros['situacoes']);
        }

        if (!empty($filtros['distritos'])) {
            $query->whereIn('distrito', $filtros['distritos']);
        }

        if (!empty($filtros['subprefeituras'])) {
            $query->whereIn('subprefeitura', $filtros['subprefeituras']);
        }

        // return $query->get();
        return $query;
    }


    public function buscarProcessos(array $filtros, bool $retornarQuery = false)
    {
        // return [
        //     'debug' => true,
        //     'filtros' => $filtros,
        // ];

        $tSqlIncra = $this->prefixoSchema() . $this->viewSqlIncra;              // dbo.prata_sql_incra
        $tAssunto  = $this->prefixoSchema() . $this->viewAssuntos;              // dbo.prata_assunto
        $tInteressado  = $this->prefixoSchema() . $this->viewInteressados;      // dbo.prata_interessado

        $query = DB::connection($this->connection)
            ->table($tSqlIncra . ' as sqlincra')
            ->join($tAssunto . ' as passunto', 'passunto.id_prata_assunto', '=', 'sqlincra.id_prata_assunto')
            ->leftJoin($tInteressado . ' as interessado', 'interessado.id_prata_assunto', '=', 'sqlincra.id_prata_assunto')
            ->select([
                'sqlincra.id_prata_sql_incra as id',
                'sqlincra.sql_incra as sql',

                // $this->getInteressadosRaw(),
                DB::raw('MAX(interessado.nomeInteressado) as interessados'),

                'passunto.id_prata_assunto',
                'passunto.sistema',
                'passunto.processo',
                'passunto.assunto',
                'passunto.SituacaoAssunto',
                'passunto.dtPedidoProtocolo',
                'passunto.subprefeitura',
                'passunto.distrito',
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
                'passunto.subprefeitura',
                'passunto.distrito',
                'passunto.aditivo',
            )
            ->where('passunto.dtPedidoProtocolo', '>=', $filtros['dataInicio']);

        if (!empty($filtros['dataFim'])) {
            $query->where('passunto.dtPedidoProtocolo', '<=', $filtros['dataFim']);
        }

        if (!empty($filtros['assuntos'])) {
            $query->whereIn('passunto.assunto', $filtros['assuntos']);
        }

        if (!empty($filtros['situacoes'])) {
            $query->whereIn('passunto.SituacaoAssunto', $filtros['situacoes']);
        }

        if (!empty($filtros['distritos'])) {
            $query->whereIn('passunto.distrito', $filtros['distritos']);
        }

        if (!empty($filtros['subprefeituras'])) {
            $query->whereIn('passunto.subprefeitura', $filtros['subprefeituras']);
        }

        if (!empty($filtros['interessado'])) {
            $query->where(
                'interessado.nomeInteressado',
                'like',
                '%' . $filtros['interessado'] . '%'
            );
        }

        if ($retornarQuery) {
            return DB::connection($this->connection)
                ->query()
                ->fromSub($query, 'base')
                ->orderBy('base.id_prata_assunto');
        }
        return $query->get();
    }



    public function buscarRaw(string $sqlIncra): ?array
    {
        $sql = 'SELECT TOP 1 * FROM ' . $this->prefixoSchema() . $this->viewSqlIncra . ' WHERE sql_incra = ?';
        $rows = DB::connection($this->connection)->select($sql, [$sqlIncra]);

        if (empty($rows)) {
            return null;
        }

        return (array) $rows[0];
    }


    public function listarFiltros()
    {
        $cacheKey = 'bi:filtros:processos';

        return Cache::remember($cacheKey, now()->addHour(), function () {
            $baseQuery = DB::connection($this->connection)
                ->table("{$this->prefixoSchema()}{$this->viewAssuntos}");

            return [
                'assuntos' => $baseQuery
                    ->clone()
                    ->select('assunto')
                    ->whereNotNull('assunto')
                    ->distinct()
                    ->orderBy('assunto')
                    ->pluck('assunto')
                    ->values(),

                'situacoes' => $baseQuery
                    ->clone()
                    ->select('SituacaoAssunto')
                    ->whereNotNull('SituacaoAssunto')
                    ->distinct()
                    ->orderBy('SituacaoAssunto')
                    ->pluck('SituacaoAssunto')
                    ->values(),

                'distritos' => $baseQuery
                    ->clone()
                    ->select('distrito')
                    ->whereNotNull('distrito')
                    ->distinct()
                    ->orderBy('distrito')
                    ->pluck('distrito')
                    ->values(),

                'subprefeituras' => $baseQuery
                    ->clone()
                    ->select('subprefeitura')
                    ->whereNotNull('subprefeitura')
                    ->distinct()
                    ->orderBy('subprefeitura')
                    ->pluck('subprefeitura')
                    ->values(),
            ];
        });
    }
}
