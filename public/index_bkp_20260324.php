<?php
// Exemplo de URL: https://dapl.prefeitura.sp.gov.br/?processo=2004-14069-05

$processo = $_GET['processo'] ?? null;

if (!$processo || !preg_match('/^\d{4}-\d+(?:-\d+)*$/', $processo)) {
    http_response_code(400);
    echo "Parâmetro 'processo' inválido.";
    exit;
}

// Extrai o ano (primeiros 4 dígitos)
$ano = substr($processo, 0, 4);

// Caminho do arquivo ZIP
$zipPath = "/var/www/dapl/storage/app/certificados/{$ano}.zip";
$arquivoDentroDoZip = "{$processo}.pdf";

if (!file_exists($zipPath)) {
    try {
        $zipPath = "/var/www/dapl/storage/app/certificados/outros.zip";
        $zip = new ZipArchive;
        if ($zip->open($zipPath) === TRUE) {
            $conteudo = $zip->getFromName($arquivoDentroDoZip);

            if ($conteudo === false) {
                http_response_code(404);
                echo "Arquivo '{$arquivoDentroDoZip}' nao encontrado.";
                $zip->close();
                exit;
            }

            // Força o download do arquivo PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($arquivoDentroDoZip) . '"');
            header('Content-Length: ' . strlen($conteudo));
            echo $conteudo;

            $zip->close();
        }
    } catch (\Throwable $th) {
        //throw $th;
        http_response_code(404);
        echo "Pasta nao encontrada.";
        exit;
    }
    http_response_code(404);
    echo "Pasta nao encontrada.";
    exit;
}

$zip = new ZipArchive;
if ($zip->open($zipPath) === TRUE) {
    $conteudo = $zip->getFromName($arquivoDentroDoZip);

    if ($conteudo === false) {
        http_response_code(404);
        echo "Arquivo '{$arquivoDentroDoZip}' nao encontrado.";
        $zip->close();
        exit;
    }

    // Força o download do arquivo PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($arquivoDentroDoZip) . '"');
    header('Content-Length: ' . strlen($conteudo));
    echo $conteudo;

    $zip->close();
} else {
    http_response_code(500);
    echo "Erro ao abrir o arquivo ZIP.";
}
