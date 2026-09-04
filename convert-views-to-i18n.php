<?php

/**
 * Laravel Blade Translation Converter — com DRY-RUN e filtros inteligentes
 *
 * Converte textos hard-coded em português nas views Blade para {{ __('texto') }}
 * e gera o arquivo lang/pt.json com as strings encontradas.
 *
 * USO:
 *   php convert-views-to-i18n.php           → executa a conversão (com backup)
 *   php convert-views-to-i18n.php --dry-run → só mostra o que seria alterado
 */

$dryRun = in_array('--dry-run', $argv ?? []);

$viewsPath = __DIR__ . '/resources/views';
$backupPath = __DIR__ . '/resources/views_backup_' . date('Ymd_His');
$langPath = __DIR__ . '/lang';
$langFile = $langPath . '/pt.json';

// Atributos HTML que costumam ter textos traduzíveis
// NOTA: 'value' foi REMOVIDO intencionalmente — é dado, não texto de interface
$translatableAttributes = [
    'placeholder', 'title', 'alt', 'label',
    'data-content', 'data-title', 'insertlabel', 'insertplaceholder', 'help',
];

// Nomes próprios do sistema — não traduzir
$properNames = [
    'lunar base', 'lunar', 'vlibras', 'lucide',
    'lunar base - laravel starter kit',
];

// Identificadores técnicos (em inglês ou pt) — não traduzir
$technicalIdentifiers = [
    'draft', 'published', 'archived', 'admin', 'editor', 'front', 'core', 'plugin',
    'skip', 'overwrite', 'true', 'false', 'all', 'none', 'left', 'right', 'center',
    'float-left', 'float-right', 'image', 'document', 'orphan', 'linked',
    'taxonomies', 'breadcrumb', 'fix', 'nextpageurl', 'all', 'front', 'admin',
    'core', 'plugin', 'image', 'document', 'orphan', 'linked', 'none', 'left',
    'center', 'right', 'float-left', 'float-right', 'draft', 'published', 'archived',
    'skip', 'overwrite', 'taxonomies', 'true', 'false',
];

// Preposições e conjunções que indicam fragmento de frase
$fragmentStarters = [
    'a', 'ao', 'aos', 'à', 'às', 'ante', 'após', 'apos', 'até', 'ate', 'com', 'contra',
    'de', 'do', 'dos', 'da', 'das', 'desde', 'durante', 'e', 'em', 'entre', 'excepto',
    'mas', 'mediante', 'nem', 'no', 'nos', 'na', 'nas', 'o', 'os', 'ou', 'para', 'per',
    'perante', 'por', 'pra', 'pro', 'pelo', 'pelos', 'pela', 'pelas', 'porque', 'pois',
    'posto', 'salvo', 'segundo', 'sem', 'sob', 'sobre', 'trás', 'tras', 'também', 'tambem',
    'além', 'alem', 'antes', 'apenas', 'ainda', 'assim', 'bem', 'caso', 'como', 'conforme',
    'conquanto', 'contudo', 'cuja', 'cujo', 'cujas', 'cujos', 'da mesma forma', 'depois',
    'desta', 'deste', 'diferente', 'embora', 'enquanto', 'então', 'entao', 'exceto',
    'igualmente', 'ja', 'já', 'logo', 'mal', 'mas', 'mesmo', 'não', 'nao', 'nem', 'nesta',
    'neste', 'nessa', 'nesse', 'no entanto', 'obstante', 'ora', 'outrossim', 'pelo',
    'porém', 'porem', 'portanto', 'posteriormente', 'precisamente', 'primeiramente',
    'principalmente', 'provavelmente', 'quaisquer', 'quaisquer que', 'qualquer',
    'qualquer que', 'quando', 'quanto', 'quanto a', 'quanto mais', 'quanto menos',
    'quase', 'que', 'quem', 'quer', 'se', 'seja', 'sempre', 'senão', 'senao', 'seu',
    'sim', 'sobretudo', 'tal', 'tampouco', 'tanto', 'tanto faz', 'tanto quanto',
    'tao', 'tão', 'todavia', 'toda', 'todas', 'todo', 'todos', 'tornado', 'tornar',
    'tornou', 'trata', 'tratam', 'tratando', 'tratava', 'tratavam', 'trate', 'tratem',
    'tratou', 'trouxeram', 'trouxe', 'tudo', 'tão logo', 'ultimamente', 'um', 'uma',
    'umas', 'uns', 'visto', 'visto que', 'volta', 'voltam', 'voltando', 'voltar',
    'voltaram', 'voltava', 'voltavam', 'volte', 'voltem', 'voltou', 'à medida que',
    'à medida', 'às vezes', 'às', 'á', 'ão', 'ê', 'ô',
];

// Textos que NÃO devem ser convertidos
$skipPatterns = [
    '/^\s*$/u',                           // vazio ou só espaços
    '/^[\d\s\W]+$/u',                    // só números e símbolos
    '/^\s*[\{\}\[\]\(\)<>\/\\=]+\s*$/u', // símbolos de código
    '/^\s*[@#]\w+/u',                    // diretivas blade ou hashtags
    '/\{\{\s*.*\s*\}\}/u',              // já tem blade echo
    '/\{!!\s*.*\s*!!\}/u',              // já tem blade unescaped
    '/^\s*https?:\/\//u',                // URLs
    '/^\s*\d+\s*$/u',                    // só números
    '/^\s*[\-\+\*\#\>\|]+\s*$/u',       // bullets e separadores
    '/^\s*nextpageurl/i',                // código PHP/Blade
    '/\(\s*ex[:\s]/i',                  // "(ex:" ou "(ex "
    '/\)\s*ou\s/i',                     // ") ou " — fragmento
    '/\)\s*e\s/i',                      // ") e " — fragmento
];

function looksLikePortuguese(string $text): bool
{
    $trimmed = trim($text);
    if (strlen($trimmed) < 2 || strlen($trimmed) > 200) {
        return false;
    }

    // Tem caracteres acentuados comuns em português?
    if (preg_match('/[áàãâäéèêëíìîïóòõôöúùûüçÁÀÃÂÄÉÈÊËÍÌÎÏÓÒÕÔÖÚÙÛÜÇ]/u', $trimmed)) {
        return true;
    }

    // Ou é um texto curto de interface (sem código, sem variáveis)
    if (preg_match('/^[a-zA-Z\s\-]+$/u', $trimmed) && strlen($trimmed) < 50) {
        return true;
    }

    return false;
}

function isEnglishText(string $text): bool
{
    $trimmed = strtolower(trim($text));
    // Se não tem acentos e parece inglês comum
    if (preg_match('/^[a-z\s\-]+$/u', $trimmed) && !preg_match('/[áàãâäéèêëíìîïóòõôöúùûüç]/u', $trimmed)) {
        // Palavras comuns em inglês que indicam que é inglês
        $englishWords = ['clear', 'preview', 'close', 'loading', 'breadcrumb', 'submit', 'cancel', 'delete', 'edit', 'create', 'update', 'save', 'back', 'next', 'previous', 'search', 'filter', 'sort', 'order', 'asc', 'desc', 'true', 'false', 'yes', 'no', 'on', 'off', 'enabled', 'disabled', 'active', 'inactive', 'visible', 'hidden', 'public', 'private', 'draft', 'published', 'archived', 'pending', 'approved', 'rejected', 'success', 'error', 'warning', 'info', 'danger', 'primary', 'secondary', 'default', 'light', 'dark'];
        $words = preg_split('/\s+/', $trimmed);
        foreach ($words as $word) {
            if (in_array($word, $englishWords)) {
                return true;
            }
        }
    }
    return false;
}

function isFragment(string $text): bool
{
    $trimmed = trim($text);

    // Termina com ( ou : — provavelmente início de frase quebrada
    if (preg_match('/[\(:]\s*$/u', $trimmed)) {
        return true;
    }

    // Começa com ) ou . — provavelmente fim de frase quebrada
    if (preg_match('/^\s*[\)\.]/u', $trimmed)) {
        return true;
    }

    // Começa com preposição/conjunção (lista reduzida das mais comuns)
    $lower = strtolower($trimmed);
    $firstWord = explode(' ', $lower)[0];
    $starters = [
        'a', 'ao', 'aos', 'à', 'às', 'ante', 'após', 'apos', 'até', 'ate', 'com', 'contra',
        'de', 'do', 'dos', 'da', 'das', 'desde', 'e', 'em', 'entre', 'mas', 'no', 'nos',
        'na', 'nas', 'o', 'os', 'ou', 'para', 'per', 'por', 'pra', 'pro', 'pelo', 'pelos',
        'pela', 'pelas', 'pois', 'porque', 'salvo', 'sem', 'sob', 'sobre', 'também', 'tambem',
        'ainda', 'antes', 'apenas', 'assim', 'bem', 'caso', 'como', 'contudo', 'depois',
        'enquanto', 'então', 'entao', 'exceto', 'igualmente', 'já', 'ja', 'logo', 'mal',
        'mas', 'mesmo', 'não', 'nao', 'nem', 'no entanto', 'obstante', 'ora', 'outrossim',
        'portanto', 'posteriormente', 'primeiramente', 'provavelmente', 'quando', 'quanto',
        'quase', 'que', 'quem', 'quer', 'se', 'seja', 'sempre', 'senão', 'senao', 'sim',
        'sobretudo', 'tal', 'tampouco', 'tanto', 'tao', 'tão', 'todavia', 'toda', 'todas',
        'todo', 'todos', 'tudo', 'um', 'uma', 'umas', 'uns', 'visto', 'volta', 'voltam',
        'voltando', 'voltar', 'voltaram', 'voltou', 'à', 'á',
    ];
    if (in_array($firstWord, $starters) && strlen($trimmed) < 80) {
        return true;
    }

    // Texto muito curto (menos de 3 palavras) que parece fragmento
    $wordCount = str_word_count($trimmed);
    if ($wordCount < 3 && strlen($trimmed) < 25) {
        // Se começa com letra minúscula, provavelmente é fragmento
        if (preg_match('/^[a-z]/u', $trimmed)) {
            return true;
        }
    }

    return false;
}

function isProperName(string $text): bool
{
    global $properNames;
    return in_array(strtolower(trim($text)), $properNames);
}

function isTechnicalIdentifier(string $text): bool
{
    global $technicalIdentifiers;
    return in_array(strtolower(trim($text)), $technicalIdentifiers);
}

function shouldSkip(string $text): bool
{
    global $skipPatterns;
    foreach ($skipPatterns as $pattern) {
        if (preg_match($pattern, $text)) {
            return true;
        }
    }
    return false;
}

function isInsideSkipTag(string $beforeText): bool
{
    $lower = strtolower($beforeText);
    foreach (['script', 'style', 'pre', 'code', 'textarea'] as $tag) {
        $openCount = substr_count($lower, "<$tag");
        $closeCount = substr_count($lower, "</$tag>");
        if ($openCount > $closeCount) {
            return true;
        }
    }
    return false;
}

// Criar diretórios necessários
if (!is_dir($langPath)) {
    if (!$dryRun) {
        mkdir($langPath, 0755, true);
    }
}

if (!$dryRun && !is_dir($backupPath)) {
    mkdir($backupPath, 0755, true);
}

$allTranslations = [];
if (file_exists($langFile)) {
    $allTranslations = json_decode(file_get_contents($langFile), true) ?: [];
}

$processedFiles = 0;
$convertedStrings = 0;
$modifiedFiles = 0;
$skippedByFilter = 0;

function processDirectory(string $dir, string $backupDir, callable $processor): void
{
    $items = glob($dir . '/*');
    foreach ($items as $item) {
        $relative = str_replace($dir . '/', '', $item);
        $backupItem = $backupDir . '/' . $relative;

        if (is_dir($item)) {
            if (!$GLOBALS['dryRun'] && !is_dir($backupItem)) {
                mkdir($backupItem, 0755, true);
            }
            processDirectory($item, $backupItem, $processor);
        } else {
            $processor($item, $backupItem);
        }
    }
}

// Cores para terminal
$green = "\033[32m";
$yellow = "\033[33m";
$red = "\033[31m";
$dim = "\033[2m";
$reset = "\033[0m";
$cyan = "\033[36m";

if ($dryRun) {
    echo $yellow . "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║  MODO DRY-RUN: Nenhum arquivo será modificado                ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝" . $reset . "\n\n";
} else {
    echo $green . "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║  MODO EXECUÇÃO: Backup será criado em views_backup_*         ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝" . $reset . "\n\n";
}

processDirectory($viewsPath, $backupPath, function ($filePath, $backupPath) use (
    &$processedFiles, &$convertedStrings, &$allTranslations,
    &$modifiedFiles, &$skippedByFilter, $translatableAttributes, $dryRun, $green, $yellow, $dim, $reset, $cyan
) {
    if (!str_ends_with($filePath, '.blade.php')) {
        return;
    }

    $content = file_get_contents($filePath);
    $originalContent = $content;

    if (!$dryRun) {
        file_put_contents($backupPath, $originalContent);
    }

    $processedFiles++;
    $fileChanges = [];

    // ============================================
    // 1. Converter textos entre tags HTML
    // ============================================
    $content = preg_replace_callback(
        '/>([^<\n]{2,200})</u',
        function ($matches) use (&$convertedStrings, &$allTranslations, &$fileChanges, &$skippedByFilter) {
            $fullMatch = $matches[0];
            $text = $matches[1];

            if (shouldSkip($text) || !looksLikePortuguese($text)) {
                return $fullMatch;
            }

            $trimmed = trim($text);
            if (empty($trimmed)) {
                return $fullMatch;
            }

            // Filtros inteligentes
            if (isProperName($trimmed)) {
                $skippedByFilter++;
                return $fullMatch;
            }
            if (isTechnicalIdentifier($trimmed)) {
                $skippedByFilter++;
                return $fullMatch;
            }
            if (isEnglishText($trimmed)) {
                $skippedByFilter++;
                return $fullMatch;
            }
            if (isFragment($trimmed)) {
                $skippedByFilter++;
                return $fullMatch;
            }

            $allTranslations[$trimmed] = $trimmed;
            $convertedStrings++;

            $before = substr($text, 0, strpos($text, $trimmed));
            $after = substr($text, strpos($text, $trimmed) + strlen($trimmed));

            $replacement = '>' . $before . "{{ __('" . addslashes($trimmed) . "') }}" . $after . '<';

            $fileChanges[] = [
                'tipo' => 'texto',
                'original' => $trimmed,
                'novo' => "{{ __('" . addslashes($trimmed) . "') }}",
            ];

            return $replacement;
        },
        $content
    );

    // ============================================
    // 2. Converter atributos HTML traduzíveis
    // ============================================
    foreach ($translatableAttributes as $attr) {
        $pattern = '/(' . preg_quote($attr, '/') . ')=(["\'])([^"\']{2,200})\2/ui';
        $content = preg_replace_callback(
            $pattern,
            function ($matches) use (&$convertedStrings, &$allTranslations, &$fileChanges, &$skippedByFilter) {
                $attrName = $matches[1];
                $quote = $matches[2];
                $text = $matches[3];

                if (shouldSkip($text) || !looksLikePortuguese($text)) {
                    return $matches[0];
                }

                $trimmed = trim($text);

                // Filtros inteligentes
                if (isProperName($trimmed)) {
                    $skippedByFilter++;
                    return $matches[0];
                }
                if (isTechnicalIdentifier($trimmed)) {
                    $skippedByFilter++;
                    return $matches[0];
                }
                if (isEnglishText($trimmed)) {
                    $skippedByFilter++;
                    return $matches[0];
                }
                if (isFragment($trimmed)) {
                    $skippedByFilter++;
                    return $matches[0];
                }

                $allTranslations[$trimmed] = $trimmed;
                $convertedStrings++;

                $replacement = ':' . $attrName . '=' . $quote . "{{ __('" . addslashes($trimmed) . "') }}" . $quote;

                $fileChanges[] = [
                    'tipo' => 'atributo',
                    'original' => $attrName . '=' . $quote . $text . $quote,
                    'novo' => $replacement,
                ];

                return $replacement;
            },
            $content
        );
    }

    // ============================================
    // 3. Converter textos soltos no início de linha (fora de tags)
    // ============================================
    $content = preg_replace_callback(
        '/^(\s*)([A-ZÀ-Ú][A-Za-zÀ-ÖØ-öø-ÿ\s\-,\.]{2,100})(\s*)$/mu',
        function ($matches) use (&$convertedStrings, &$allTranslations, &$fileChanges, &$skippedByFilter) {
            $indent = $matches[1];
            $text = trim($matches[2]);
            $trailing = $matches[3];

            if (shouldSkip($text) || !looksLikePortuguese($text)) {
                return $matches[0];
            }

            // Filtros inteligentes
            if (isProperName($text)) {
                $skippedByFilter++;
                return $matches[0];
            }
            if (isTechnicalIdentifier($text)) {
                $skippedByFilter++;
                return $matches[0];
            }
            if (isEnglishText($text)) {
                $skippedByFilter++;
                return $matches[0];
            }
            if (isFragment($text)) {
                $skippedByFilter++;
                return $matches[0];
            }

            $allTranslations[$text] = $text;
            $convertedStrings++;

            $fileChanges[] = [
                'tipo' => 'linha',
                'original' => $text,
                'novo' => "{{ __('" . addslashes($text) . "') }}",
            ];

            return $indent . "{{ __('" . addslashes($text) . "') }}" . $trailing;
        },
        $content
    );

    // ============================================
    // Relatório por arquivo
    // ============================================
    if ($content !== $originalContent) {
        $modifiedFiles++;
        $relativePath = str_replace(__DIR__ . '/', '', $filePath);

        if ($dryRun) {
            echo $yellow . "📄 " . $relativePath . $reset . "\n";
            foreach ($fileChanges as $change) {
                $icon = $change['tipo'] === 'atributo' ? '🔧' : ($change['tipo'] === 'linha' ? '📝' : '🏷️');
                echo "   $icon " . $dim . $change['original'] . $reset . "\n";
                echo "      → " . $green . $change['novo'] . $reset . "\n";
            }
            echo "\n";
        } else {
            file_put_contents($filePath, $content);
            echo $green . "  ✓ Convertido: " . $relativePath . $reset . "\n";
        }
    }
});

// ============================================
// Salvar arquivo de traduções (mesmo em dry-run, gera preview)
// ============================================
if (!$dryRun) {
    file_put_contents(
        $langFile,
        json_encode($allTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n"
    );
}

// ============================================
// Resumo
// ============================================
echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
if ($dryRun) {
    echo $yellow . "  MODO DRY-RUN — Nenhum arquivo foi modificado" . $reset . "\n";
} else {
    echo $green . "  MODO EXECUÇÃO — Conversão aplicada" . $reset . "\n";
}
echo "═══════════════════════════════════════════════════════════════\n";
echo "  Arquivos processados:  $processedFiles\n";
echo "  Arquivos modificados:  $modifiedFiles\n";
echo "  Strings convertidas:   $convertedStrings\n";
echo "  Strings filtradas:     $skippedByFilter\n";

if (!$dryRun) {
    echo "  Backup salvo em:       " . str_replace(__DIR__ . '/', '', $backupPath) . "\n";
    echo "  Traduções em:          lang/pt.json\n";
}

echo "\n";

if ($dryRun) {
    echo "  Para aplicar as alterações, rode sem --dry-run:\n";
    echo "  " . $green . "php convert-views-to-i18n.php" . $reset . "\n\n";
    echo "  Filtros ativos:\n";
    echo "  • Ignora atributos 'value=' (dados, não texto)\n";
    echo "  • Ignora nomes próprios do sistema (Lunar Base, Vlibras...)\n";
    echo "  • Ignora identificadores técnicos (draft, published, admin...)\n";
    echo "  • Ignora textos já em inglês (Clear, Preview...)\n";
    echo "  • Ignora fragmentos de frase (começa com preposição, termina com :)\n";
    echo "  • Ignora código PHP/Blade misturado com texto\n\n";
} else {
    echo "  Próximos passos:\n";
    echo "  1. Revise os arquivos convertidos (diff vs backup)\n";
    echo "  2. Ajuste manualmente casos que o script não pegou direito\n";
    echo "  3. Copie lang/pt.json para lang/en.json e traduza os valores\n";
    echo "  4. Quando estiver tudo certo, delete o backup\n";
}
echo "\n";
