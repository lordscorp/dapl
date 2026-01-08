<?php

namespace App\Services;

use App\Models\Log;

class LogService
{
    /**
     * Registra um log simples.
     *
     * @param  string       $acao
     * @param  string|null  $detalhes
     * @param  string|null  $nome
     * @param  string|null  $rf
     * @return \App\Models\Log
     */
    public function registrar(string $acao, ?string $rf = null, ?string $nome = null, ?string $detalhes = null): Log
    {
        return Log::create([
            'rf'       => $rf,
            'nome'     => $nome,
            'acao'     => $acao,
            'detalhes' => $detalhes,
        ]);
    }

    /**
     * Atalho para registrar a partir de dados de sessão (se existirem).
     *
     * @param  string|null $acao
     * @param  string|null $detalhes
     * @return \App\Models\Log|null
     */
    public function registrarDaSessao(string $acao, ?string $detalhes = null): ?Log
    {

        $rf   = session()->get('IDUsuario') ?? ($_SESSION['IDUsuario'] ?? null);
        $nome = session()->get('nomeUsuario') ?? ($_SESSION['nomeUsuario'] ?? null);

        if (empty($rf) || empty($nome)) {
            return null; // evita criação sem identificar usuário
        }

        return $this->registrar($acao, $rf, $nome, $detalhes);
    }
}
