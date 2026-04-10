<?php
/**
 * merge_views.php
 * Varrer recursivamente todos os .blade.php a partir da pasta onde o script for salvo
 * e concatenar em um único arquivo views.txt
 */

$targetDir = __DIR__;
$outputFile = $targetDir . DIRECTORY_SEPARATOR . 'views.txt';
$extension = '.blade.php';

// 🧹 1. Limpa ou cria o arquivo de saída
if (file_put_contents($outputFile, '') === false) {
    die("❌ Erro ao criar/limpar: {$outputFile}\nVerifique permissões.\n");
}

// 🔍 2. Busca recursiva robusta (funciona em Windows, Linux, qualquer versão moderna)
$files = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($targetDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), $extension)) {
        $files[] = $file->getPathname();
    }
}

if (empty($files)) {
    die("⚠️ Nenhum arquivo {$extension} encontrado em: {$targetDir}\n");
}

// Opcional: ordenar alfabeticamente para facilitar leitura
sort($files, SORT_STRING);

// 📝 3. Escreve no formato solicitado
$count = 0;
foreach ($files as $file) {
    // Caminho relativo a partir da pasta do script
    $relativePath = str_replace(rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, '', $file);
    $relativePath = str_replace('\\', '/', $relativePath); // Padroniza barras para /

    $content = file_get_contents($file);
    if ($content === false) {
        echo "⚠️ Falha ao ler: {$relativePath}\n";
        continue;
    }

    // Formato: path\nconteúdo\n\n
    file_put_contents($outputFile, $relativePath . "\n" . $content . "\n\n", FILE_APPEND);
    $count++;
}

echo "✅ Concluído! {$count} arquivos processados.\n";
echo "📄 Saída salva em: {$outputFile}\n";
