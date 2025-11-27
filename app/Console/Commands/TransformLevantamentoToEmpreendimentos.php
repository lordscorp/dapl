<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class TransformLevantamentoToEmpreendimentos extends Command
{
    protected $signature = 'transform:empreendimentos';
    protected $description = 'Transforma dados de levantamentohis para empreendimentos em lotes de 1000 registros';

    public function handle()
    {
        $batchSize = 1000;
        $offset = 0;
        $totalProcessed = 0;

        function obterArea($prioritarioValor, $items, $campo)
        {
            $normalize = fn($v) => str_replace(',', '.', trim($v));

            $valorPrioritario = $normalize($prioritarioValor);
            if (is_numeric($valorPrioritario) && $valorPrioritario > 0) {
                return (float) $valorPrioritario;
            }

            $valoresValidos = $items->pluck($campo)
                ->map($normalize)
                ->filter(fn($v) => is_numeric($v) && $v > 0);

            return $valoresValidos->isNotEmpty() ? (float) $valoresValidos->min() : null;
        }

        while (true) {

            // Buscar até 1000 grupos por lote
            $groups = DB::table('levantamentohis')
                ->select('GrupoAssuntoReferenciado')
                ->groupBy('GrupoAssuntoReferenciado')
                ->orderBy('GrupoAssuntoReferenciado')
                ->skip($offset)
                ->take($batchSize)
                ->pluck('GrupoAssuntoReferenciado');

            if ($groups->isEmpty()) {
                break; // Não há mais grupos
            }

            // Buscar registros desses grupos
            $records = DB::table('levantamentohis')
                ->whereIn('GrupoAssuntoReferenciado', $groups)
                ->orderBy('GrupoAssuntoReferenciado')
                ->get();

            if ($records->isEmpty()) {
                break;
            }

            $logFileName = 'log_transformation.txt';
            $logPath = base_path($logFileName);
            File::put($logPath, "Log de transformação\n");

            DB::beginTransaction();

            try {
                $grouped = $records->groupBy('GrupoAssuntoReferenciado');

                foreach ($grouped as $grupo => $items) {
                    // Escolher registro prioritário
                    $prioritario = $items->firstWhere('assunto', 'LIKE', 'Alvará%Edificação Nova') ?? $items->sortByDesc('dtEmissao')->first();

                    $data = [
                        'dataPrimeiroRegistro' => $items->min('dt_autuacao'),
                        'dataUltimoRegistro' => $items->max('dtEmissao'),
                        'grupoAssuntoReferenciado' => $grupo,
                        'sqlIncra' => $prioritario->sql_incra ?? $items->sortByDesc('dtEmissao')->first()->sql_incra,
                        'nomeInteressado' => $prioritario->nomeInteressado ?? $items->sortByDesc('dtEmissao')->first()->nomeInteressado,
                        'endereco' => $prioritario->Logradouros ?? $items->sortByDesc('dtEmissao')->first()->Logradouros,
                        'areaTerreno'     => obterArea($prioritario->P_QTD_TERR_REAL, $items, 'P_QTD_TERR_REAL'),
                        'areaConstruida'  => obterArea($prioritario->P_QTD_AREA_CNSR, $items, 'P_QTD_AREA_CNSR'),
                        'areaComputavel'  => obterArea($prioritario->P_QTD_AREA_CMPL, $items, 'P_QTD_AREA_CMPL'),
                        'blocos' => $prioritario->blocos,
                        'pavimentos' => $prioritario->pavimentos,
                        'amparoLegal' => $prioritario->amparoLegal,
                        'usoDoImovel' => $prioritario->usoDoImovel,
                        'constaOutorga' => $prioritario->constaOutorga,
                        'zoneamento' => $prioritario->zoneamento,
                        'num_HIS' => $prioritario->num_HIS,
                        'num_HMP' => $prioritario->num_HMP,
                        'num_EHIS' => $prioritario->num_EHIS,
                        'num_EHMP' => $prioritario->num_EHMP,
                        'num_R1' => $prioritario->num_R1,
                        'num_R2' => $prioritario->num_R2,
                        'num_nRa' => $prioritario->num_nRa,
                        'num_nR1' => $prioritario->num_nR1,
                        'num_nR2' => $prioritario->num_nR2,
                        'num_nR3' => $prioritario->num_nR3,
                        'num_Ind1a' => $prioritario->num_Ind1a,
                        'num_Ind1b' => $prioritario->num_Ind1b,
                        'num_Ind2' => $prioritario->num_Ind2,
                        'num_Ind3' => $prioritario->num_Ind3,
                        'num_INFRA' => $prioritario->num_INFRA,
                        'proprietario' => $prioritario->proprietario,
                        'zonaUso' => $prioritario->zona_uso,
                        'subprefeitura' => $prioritario->subprefeitura,
                        'nomeValidador' => $prioritario->nomeValidador ?? null,
                        'rfValidador' => $prioritario->rfValidador ?? null,
                        'validadoEm' => $prioritario->validadoEm ?? null,
                    ];

                    // Inserir no banco
                    $id = DB::table('empreendimentos')->insertGetId($data);

                    // Log de problemas
                    $problemas = [];
                    foreach ($data as $campo => $valor) {
                        if (empty($valor)) {
                            $problemas[] = $campo;
                        }
                    }
                    File::append($logPath, "Registro ID {$id} - Campos vazios: " . implode(', ', $problemas) . "\n");

                    $totalProcessed++;
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Erro: " . $e->getMessage());
                File::append($logPath, $e->getMessage() . "\n\n");
                return;
            }

            $offset += $batchSize;
        }

        $this->info("Transformação concluída. Total processado: {$totalProcessed}");
    }
}
