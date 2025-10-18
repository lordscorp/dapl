<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class ValidadosExport implements FromCollection
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
}