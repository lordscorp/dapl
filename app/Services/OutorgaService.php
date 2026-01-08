<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;

class OutorgaService
{

    protected LogService $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

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
            // throw new \InvalidArgumentException("Area computavel menor que area total gera OODC invalida. ac, at $ac , $at");
            return 0;
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
        $detalhesLog = "Ano: {$ano}, SQL: {$sql}, codlog: {$codlog}";
        try {
            $codlog = str_replace('-', '', $codlog);
        }
        catch (Exception $e) {
            $this->logService->registrar("Consultar valor m2", null, null, $e);
        }
        $this->logService->registrar("Consultar valor m2", null, null, $detalhesLog);
        // Reatribui usando a função já existente
        $sqlFormatado = $this->formatarSQL($sql);

        // Quebra pelo ponto
        $partes = explode('.', $sqlFormatado);

        // Valida se temos pelo menos 3 partes
        if (count($partes) < 3) {
            return null; // ou lançar exceção
        }

        // Extrai setor e quadra
        $setor = substr($partes[0], 0, 3);
        $quadra = substr($partes[1], 0, 3);

        $anoTabela = 0;

        if ($ano >= 2025) {
            $anoTabela = 2025;
        } elseif ($ano === 2024) {
            $anoTabela = 2024;
        } elseif ($ano === 2023) {
            $anoTabela = 2023;
        } elseif ($ano >= 2020 && $ano <= 2022) {
            $anoTabela = 2020;
        } else {
            $anoTabela = 2013;
        }


        // Monta nome da tabela dinamicamente
        $tabela = 'oodc_quadro14_vm2_' . intval($anoTabela);

        $resultado = DB::table($tabela)
            ->where('setor', $setor)
            ->where('quadra', $quadra)
            ->where('codlog', $codlog)
            ->value('vm2');


        return $resultado !== null ? (float)$resultado : null;
    }

    function consultarValorM2_old(int $ano, string $sql, string $codlog): ?float
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
        $tabela = 'oodc_quadro14_vm2_2013';

        // Consulta usando Query Builder
        $resultado = DB::table($tabela)
            ->where('setor', $setor)
            ->where('quadra', $quadra)
            ->where('codlog', $codlog)
            ->value('vm2'); // retorna apenas o valor da coluna

        $resultado = $this->calcularReajusteVm2($resultado, $ano);

        return $resultado !== null ? (float)$resultado : null;
    }

    /**
     * Calcula valor do metro quadrado consultando o reajuste anual cumulativo
     */
    function calcularReajusteVm2($valorBase, $ano)
    {
        // Lista fixa até 2025
        $reajustesFixos = [
            2020 => 2.000000,
            2021 => 0.000000,
            2022 => 0.000000,
            2023 => 5.000000,
            2024 => 5.000000,
            2025 => 4.500000
        ];

        $valorFinal = $valorBase;


        if ($ano <= 2025) {
            // Aplica reajustes da lista fixa
            foreach ($reajustesFixos as $anoReajuste => $percentual) {
                if ($anoReajuste <= $ano) {
                    $valorFinal *= (1 + ($percentual / 100));
                }
            }
        } else {
            // Primeiro aplica até 2025
            foreach ($reajustesFixos as $percentual) {
                $valorFinal *= (1 + ($percentual / 100));
            }

            // Consulta no banco para anos > 2025
            $resultados = DB::table('oodc_vm2_reajustes')
                ->select(DB::raw('percentual_reajuste, EXTRACT(YEAR FROM data_inicio) as ano_inicio'))
                ->whereRaw('EXTRACT(YEAR FROM data_inicio) > 2025 AND EXTRACT(YEAR FROM data_inicio) <= ?', [$ano])
                ->orderBy('data_inicio', 'asc')
                ->get();

            foreach ($resultados as $row) {
                $valorFinal *= (1 + ($row->percentual_reajuste / 100));
            }
        }


        return round($valorFinal, 2);
    }




    public function consultarFatorPlanejamento(string $setor, string $quadra): ?float
    {
        $macroarea = DB::table('sq_macroareas')
            ->where('cd_setor_fiscal', $setor)
            ->where('cd_quadra_fiscal', $quadra)
            ->first();
        // echo "consultarFatorPlanejamento";
        // var_dump($macroarea);
        if (!$macroarea) {
            return null;
        }

        // Determinar chave e coluna
        $perimetro = trim($macroarea->nm_perimetro_divisao_pde ?? '');
        $macro = trim($macroarea->tx_macro_divisao_pde ?? '');

        $query = DB::table('oodc_quadro6_fp');

        if ($perimetro !== '') {
            $query->where('perimetro', $perimetro);
        } elseif ($macro !== '') {
            $query->where('macroarea', $macro);
        } else {
            return null; // Nenhuma chave válida
        }

        $fator = $query->value('fp_R');

        return $fator !== null ? floatval(str_replace(',', '.', $fator)) : null;
    }

    /**
     * Busca um processo na tabela empreendimentos pelo número do processo.
     *
     * @param string $processo
     * @return array|null
     */
    public function buscarProcessoSISACOE(string $processo, float $fs = 1): ?array
    {
        // $resultado = DB::table('empreendimentos')
        //     ->where('processo', $processo)
        //     ->first();

        $resultado = DB::table('empreendimentos')
            ->join('levantamentohis', 'levantamentohis.grupoAssuntoReferenciado', '=', 'empreendimentos.grupoAssuntoReferenciado')
            ->where('levantamentohis.processo', $processo)
            ->select('empreendimentos.*')
            ->first();


        if ($resultado) {
            // Converte para array para manipulação
            $resultadoArray = (array) $resultado;

            // Extrai os campos necessários para consultarValorM2
            $ano = substr($resultadoArray['dataUltimoRegistro'] ?? $resultadoArray['dt_autuadataPrimeiroRegistro'] ?? '2014', 0, 4);

            $sql = explode(';', $resultadoArray['sqlIncra'] ?? '')[0] ?: null;

            $codlog = $resultadoArray['codlog'] ?? null;
            // Chama consultarValorM2 se os campos existirem
            if ($ano && $sql && $codlog) {
                $valorM2 = $this->consultarValorM2($ano, $sql, $codlog);
                $resultadoArray['valor_m2'] = $valorM2;
            } else {
                $resultadoArray['valor_m2'] = null;
            }

            // Extrai os campos necessários para calcularOutorga
            $at = $resultadoArray['areaTerreno'] ?? null;
            $ac = $resultadoArray['areaComputavel'] ?? null;
            $v  = $resultadoArray['valor_m2'] ?? null; // usa o valor calculado acima

            $setor = substr(str_replace('.', '', $sql), 0, 3);
            $quadra = substr(str_replace('.', '', $sql), 3, 3);

            $fp = $this->consultarFatorPlanejamento($setor, $quadra);
            $resultadoArray['fp'] = $fp;

            // Chama calcularOutorga se os campos existirem
            if ($at && $ac && $v && $fp) {
                $valorOutorga = $this->calcularOutorga((float)$at, (float)$ac, (float)$v, (float)$fs, (float)$fp);
                $resultadoArray['valor_outorga'] = $valorOutorga;
            } else {
                $resultadoArray['valor_outorga'] = null;
            }

            return $resultadoArray;
        }

        return null;
    }


    /**
     * Busca um processo na tabela tmp_processos_ad_oodc pelo número do processo.
     *
     * @param string $processo
     * @return array|null
     */
    public function buscarProcessoAD(string $processo, float $fs = 1): ?array
    {
        $resultado = DB::table('tmp_processos_ad_oodc')
            ->where('processo', $processo)
            ->first();

        if ($resultado) {
            // Converte para array para manipulação
            $resultadoArray = (array) $resultado;

            // Extrai os campos necessários para consultarValorM2
            $ano = substr($resultadoArray['dt_emissao'] ?? $resultadoArray['dt_autuacao'] ?? '2014', 0, 4);

            // $sql = $resultadoArray['sqls'] ?? null;
            $sql = explode(',', $resultadoArray['sqls'] ?? '')[0] ?: null;

            $codlog = $resultadoArray['codlog'] ?? null;
            // Chama consultarValorM2 se os campos existirem
            if ($ano && $sql && $codlog) {
                $valorM2 = $this->consultarValorM2($ano, $sql, $codlog);
                $resultadoArray['valor_m2'] = $valorM2;
            } else {
                $resultadoArray['valor_m2'] = null;
            }

            // Extrai os campos necessários para calcularOutorga
            $at = $resultadoArray['area_do_terreno'] ?? null;
            $ac = $resultadoArray['area_edificada_computavel'] ?? null;
            $v  = $resultadoArray['valor_m2'] ?? null; // usa o valor calculado acima
            // $fp = $resultadoArray['fp'] ?? null;
            $setor = substr(str_replace('.', '', $sql), 0, 3);
            $quadra = substr(str_replace('.', '', $sql), 3, 3);

            $fp = $this->consultarFatorPlanejamento($setor, $quadra);
            $resultadoArray['fp'] = $fp;

            // var_dump("FP", $fp);

            // Chama calcularOutorga se os campos existirem
            if ($at && $ac && $v && $fp) {
                $valorOutorga = $this->calcularOutorga((float)$at, (float)$ac, (float)$v, (float)$fs, (float)$fp);
                $resultadoArray['valor_outorga'] = $valorOutorga;
            } else {
                $resultadoArray['valor_outorga'] = null;
            }

            return $resultadoArray;
        }

        return null;
    }

    public function calcularProcessosSISACOE(float $fs = 1)
    {
        $this->logService->registrar("Iniciado calculo de processos SISACOE", null, null, "Fator social: " . $fs);
        $registros = DB::table('empreendimentos')
            ->where(function ($q) {
                $q->whereNotNull('num_his')
                    ->orWhereNotNull('num_his1')
                    ->orWhereNotNull('num_his2')
                    ->orWhereNotNull('num_ehis');
            })
            ->orderBy('dataUltimoRegistro', 'desc')
            ->get();

        $alterados = 0;
        $erros = [];

        DB::beginTransaction();

        try {
            foreach ($registros as $registro) {
                try {
                    // --- Preparação dos dados base ---
                    $ano = substr(($registro->dataUltimoRegistro ?? $registro->dataPrimeiroRegistro ?? '2014'), 0, 4);

                    // Primeiro SQL da lista (se houver)
                    $sqlStr = null;
                    if (!empty($registro->sqlIncra)) {
                        $partesSql = explode(';', $registro->sqlIncra);
                        $sqlStr = trim($partesSql[0]) ?: null;
                    }

                    $codlog = $registro->codlog ?? null;

                    // Valor do m²
                    $valorM2 = null;
                    if ($ano && $sqlStr && $codlog) {
                        $valorM2 = $this->consultarValorM2($ano, $sqlStr, $codlog);
                    }

                    // Campos para cálculo
                    $at = $registro->areaTerreno ?? null;
                    $ac = $registro->areaComputavel ?? null;

                    // FP (setor/quadr) a partir do SQL
                    $fp = null;
                    if ($sqlStr) {
                        $sqlNum = preg_replace('/\D/', '', $sqlStr); // remove pontos e não dígitos
                        $setor = substr($sqlNum, 0, 3) ?: null;
                        $quadra = substr($sqlNum, 3, 3) ?: null;

                        if ($setor !== null && $quadra !== null) {
                            $fp = $this->consultarFatorPlanejamento($setor, $quadra);
                        }
                    }

                    // --- Cálculo da outorga ---
                    $valorOutorga = null;
                    if ($at !== null && $ac !== null && $valorM2 !== null && $fp !== null) {
                        $valorOutorga = $this->calcularOutorga(
                            (float) $at,
                            (float) $ac,
                            (float) $valorM2,
                            (float) $fs,
                            (float) $fp
                        );

                        // Atualiza diretamente a tabela com o valor calculado
                        $updated = DB::table('empreendimentos')
                            ->where('id', $registro->id)
                            ->update([
                                'outorgaCalculada' => $valorOutorga,
                            ]);

                        $alterados += $updated;
                    } else {
                        // Loga motivo de não cálculo
                        $erros[] = [
                            'id'       => $registro->id ?? null,
                            'mensagem' => 'Campos insuficientes para cálculo de outorga',
                            'detalhes' => [
                                'ano'                  => $ano,
                                'sql'                  => $sqlStr,
                                'codlog'               => $codlog,
                                'area_do_terreno'      => $at,
                                'area_edificada_comp'  => $ac,
                                'valor_m2'             => $valorM2,
                                'fp'                   => $fp,
                            ],
                        ];
                    }
                } catch (\Throwable $e) {
                    // Erro isolado por registro
                    $erros[] = [
                        'id'        => $registro->id ?? null,
                        'mensagem'  => 'Erro ao processar registro',
                        'exception' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $erros[] = [
                'mensagem'  => 'Falha geral na transação',
                'exception' => $e->getMessage(),
            ];
            $this->logService->registrar("Falha ao calcular oodc", null, null, $e->getMessage());
        }

        // Retorna apenas a quantidade de registros alterados e erros
        return [
            'alterados' => $alterados,
            'erros'     => $erros,
        ];
    }

    public function calcularProcessosAD(int $paginacao = 1, float $fs = 1): array
    {
        $this->logService->registrar("Iniciado calculo de processos AD", null, null, "Paginacao: " . $paginacao . ". Fator social: " . $fs);
        $limite = 100;
        $offset = ($paginacao - 1) * $limite;

        // Busca com paginação
        // $registros = DB::table('tmp_processos_ad_oodc')
        //     ->whereIn('usos_registrados', ['HIS', 'HIS 1', 'HIS 2', 'HIS1'])
        //     ->orderBy('dt_autuacao', 'desc')
        //     ->offset($offset)
        //     ->limit($limite)
        //     ->get();
        $registros = DB::table('tmp_processos_ad_oodc')
            ->whereIn('usos_registrados', ['HIS', 'HIS 1', 'HIS 2', 'HIS1'])
            ->orderBy('dt_autuacao', 'desc')
            ->get();

        $alterados = 0;
        $erros = [];

        DB::beginTransaction();

        try {
            foreach ($registros as $registro) {
                try {
                    // --- Preparação dos dados base ---
                    $ano = substr(($registro->dt_emissao ?? $registro->dt_autuacao ?? '2014'), 0, 4);

                    // Primeiro SQL da lista (se houver)
                    $sqlStr = null;
                    if (!empty($registro->sqls)) {
                        $partesSql = explode(',', $registro->sqls);
                        $sqlStr = trim($partesSql[0]) ?: null;
                    }

                    $codlog = $registro->codlog ?? null;

                    // Valor do m²
                    $valorM2 = null;
                    if ($ano && $sqlStr && $codlog) {
                        $valorM2 = $this->consultarValorM2($ano, $sqlStr, $codlog);
                    }

                    // Campos para cálculo
                    $at = $registro->area_do_terreno ?? null;
                    $ac = $registro->area_edificada_computavel ?? null;

                    // FP (setor/quadr) a partir do SQL
                    $fp = null;
                    if ($sqlStr) {
                        $sqlNum = preg_replace('/\D/', '', $sqlStr); // remove pontos e não dígitos
                        $setor = substr($sqlNum, 0, 3) ?: null;
                        $quadra = substr($sqlNum, 3, 3) ?: null;

                        if ($setor !== null && $quadra !== null) {
                            $fp = $this->consultarFatorPlanejamento($setor, $quadra);
                        }
                    }

                    // --- Cálculo da outorga ---
                    $valorOutorga = null;
                    if ($at !== null && $ac !== null && $valorM2 !== null && $fp !== null) {
                        $valorOutorga = $this->calcularOutorga(
                            (float) $at,
                            (float) $ac,
                            (float) $valorM2,
                            (float) $fs,
                            (float) $fp
                        );

                        // Atualiza diretamente a tabela com o valor calculado
                        $updated = DB::table('tmp_processos_ad_oodc')
                            ->where('np', $registro->np)
                            ->update([
                                'outorga_calculada' => $valorOutorga,
                            ]);

                        $alterados += $updated;
                    } else {
                        // Loga motivo de não cálculo
                        $erros[] = [
                            'np'       => $registro->np ?? null,
                            'mensagem' => 'Campos insuficientes para cálculo de outorga',
                            'detalhes' => [
                                'ano'                  => $ano,
                                'sql'                  => $sqlStr,
                                'codlog'               => $codlog,
                                'area_do_terreno'      => $at,
                                'area_edificada_comp'  => $ac,
                                'valor_m2'             => $valorM2,
                                'fp'                   => $fp,
                            ],
                        ];
                    }
                } catch (\Throwable $e) {
                    // Erro isolado por registro
                    $erros[] = [
                        'np'        => $registro->np ?? null,
                        'mensagem'  => 'Erro ao processar registro',
                        'exception' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $erros[] = [
                'mensagem'  => 'Falha geral na transação',
                'exception' => $e->getMessage(),
            ];
            $this->logService->registrar("Falha ao calcular oodc", null, null, $e->getMessage());
        }

        // Retorna apenas a quantidade de registros alterados e erros
        return [
            'alterados' => $alterados,
            'erros'     => $erros,
        ];
    }

    public function calcularProcessosAD_bkp(int $paginacao = 1, float $fs = 1): array
    {
        // Define limite e offset para paginação
        $limite = 100;
        $offset = ($paginacao - 1) * $limite;

        // Busca registros da tabela com paginação e ordenação
        $registros = DB::table('tmp_processos_ad_oodc')
            ->orderBy('dt_autuacao', 'desc')
            ->offset($offset)
            ->limit($limite)
            ->get();

        $resultRetorno = [];

        foreach ($registros as $registro) {
            $resultadoArray = (array) $registro;

            // Extrai ano
            $ano = substr($resultadoArray['dt_emissao'] ?? $resultadoArray['dt_autuacao'] ?? '2014', 0, 4);

            // Extrai SQL e codlog
            $sql = explode(',', $resultadoArray['sqls'] ?? '')[0] ?: null;
            $codlog = $resultadoArray['codlog'] ?? null;

            // Calcula valor_m2
            if ($ano && $sql && $codlog) {
                $valorM2 = $this->consultarValorM2($ano, $sql, $codlog);
                $resultadoArray['valor_m2'] = $valorM2;
            } else {
                $resultadoArray['valor_m2'] = null;
            }

            // Extrai campos para calcular outorga
            $at = $resultadoArray['area_do_terreno'] ?? null;
            $ac = $resultadoArray['area_edificada_computavel'] ?? null;
            $v  = $resultadoArray['valor_m2'] ?? null;

            // Calcula FP
            $setor = substr(str_replace('.', '', $sql), 0, 3);
            $quadra = substr(str_replace('.', '', $sql), 3, 3);
            $fp = $this->consultarFatorPlanejamento($setor, $quadra);
            $resultadoArray['fp'] = $fp;

            // Calcula valor_outorga
            if ($at && $ac && $v && $fp) {
                $valorOutorga = $this->calcularOutorga((float)$at, (float)$ac, (float)$v, (float)$fs, (float)$fp);
                $resultadoArray['valor_outorga'] = $valorOutorga;
            } else {
                $resultadoArray['valor_outorga'] = null;
            }

            // Adiciona ao array de retorno
            $resultRetorno[] = $resultadoArray;
        }

        return $resultRetorno;
    }


    // public function buscarProcessoAD(string $processo, float $fs = 1): ?array
    // {
    //     $resultado = DB::table('tmp_processos_ad_oodc')
    //         ->where('processo', $processo)
    //         ->first();

    //     return $resultado ? (array) $resultado : null;
    // }
}
