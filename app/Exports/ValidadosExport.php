<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ValidadosExport implements FromCollection, WithHeadings
{
    protected $dados;

    public function __construct($dados)
    {
        $this->dados = $dados;
    }

    public function collection()
    {
        return $this->dados;
    }

    public function headings(): array
    {
        return [
            'processo', 'codigoPedido', 'assunto', 'dtEmissao', 'codigoPedidoReferenciado', 'documentoReferenciado', 'sql_incra', 'doc_txt', 'validando', 'validado', 'validadoEm', 'rfValidador', 'blocos', 'pavimentos', 'amparoLegal', 'usoDoImovel', 'constaOutorga', 'zoneamento', 'num_HIS', 'num_HMP', 'num_R1', 'num_R2', 'num_nR1', 'num_nR2', 'proprietario'
        ];
    }
}