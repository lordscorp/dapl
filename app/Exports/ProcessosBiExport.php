<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProcessosBiExport implements FromQuery, WithHeadings
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query->select([
            'processo',
            'sql',
            'sistema',
            'assunto',
            'SituacaoAssunto',
            'dtPedidoProtocolo',
            'distrito',
            'subprefeitura',
            'interessados',
        ]);
    }

    public function headings(): array
    {
        return [
            'Processo',
            'SQL',
            'Sistema',
            'Assunto',
            'Situação',
            'Data do Pedido',
            'Distrito',
            'Subprefeitura',
            'Interessados',
        ];
    }
}
