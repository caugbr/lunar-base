<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ScanHooksCommand extends Command
{
    /**
     * O nome e a assinatura do comando de console.
     */
    protected $signature = 'hooks:scan';

    /**
     * A descrição do comando.
     */
    protected $description = 'Varre a aplicação em busca de Hooks de Template (<x-hook>) e Hooks PHP (doAction, applyFilters) e compila o cache.';

    public function handle(): int
    {
        $startTime = microtime(true);
        $this->info('🔍 Iniciando varredura de Hooks...');

        // 1. Pastas a escanear
        $scanPaths = [
            'system' => [
                base_path('app'),
                base_path('routes'),
                base_path('resources/views'),
            ],
            'plugin' => [
                base_path('plugins'),
            ],
            'theme'  => [
                base_path('themes'),
            ],
        ];

        $templateHooks = [];
        $phpHooks      = [];

        foreach ($scanPaths as $sector => $paths) {
            foreach ($paths as $path) {
                if (!File::isDirectory($path)) {
                    continue;
                }

                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
                );

                foreach ($iterator as $file) {
                    if (!$file->isFile()) {
                        continue;
                    }

                    $filePath  = $file->getPathname();
                    $extension = $file->getExtension();

                    // Ignora arquivos que não sejam PHP/Blade
                    if ($extension !== 'php') {
                        continue;
                    }

                    $content = file_get_contents($filePath);

                    // A) Busca hooks de template apenas em arquivos Blade
                    if (str_ends_with($file->getFilename(), '.blade.php')) {
                        $this->scanTemplateHooks($content, $filePath, $sector, $templateHooks);
                    }

                    // B) Busca hooks PHP em todos os arquivos PHP (inclusive Blade)
                    $this->scanPhpHooks($content, $filePath, $sector, $phpHooks);
                }
            }
        }

        // 2. Estrutura compilada final
        $compiledData = [
            'generated_at' => now()->toIso8601String(),
            'templates'    => array_values($templateHooks),
            'php'          => array_values($phpHooks),
        ];

        // 3. Salva em bootstrap/cache/hooks.php
        $cachePath = base_path('bootstrap/cache/hooks.php');
        $fileContent = "<?php\n\nreturn " . var_export($compiledData, true) . ";\n";
        File::put($cachePath, $fileContent);

        $duration = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->table(
            ['Categoria', 'Total Encontrado'],
            [
                ['Hooks de Template (<x-hook>)', count($templateHooks)],
                ['Hooks de PHP (doAction / applyFilters)', count($phpHooks)],
                ['Tempo de Varredura', "{$duration}s"],
            ]
        );

        $this->info("✅ Cache compilado com sucesso em: bootstrap/cache/hooks.php");

        return self::SUCCESS;
    }

    /**
     * Varre tags <x-hook> em views Blade.
     */
    protected function scanTemplateHooks(string $content, string $filePath, string $sector, array &$hooks): void
    {
        preg_match_all(
            '/<x-hook\s+((?:[^>"\']|"[^"]*"|\'[^\']*\')*?)(?:\/>|>(.*?)<\/x-hook>)/is',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $attributesString = str_replace(["\r", "\n"], ' ', $match[1]);
            $innerContent     = $match[2] ?? '';

            preg_match_all('/([\w:]+)\s*=\s*("(?:[^"\\\\]|\\\\.)*"|\'(?:[^\'\\\\]|\\\\.)*\')/', $attributesString, $attrMatches, PREG_SET_ORDER);

            $attrs = [];
            foreach ($attrMatches as $m) {
                $attrs[strtolower($m[1])] = $m[2];
            }

            $rawName = $attrs['name'] ?? $attrs[':name'] ?? null;
            if (!$rawName) {
                continue;
            }

            $desc   = $attrs['desc'] ?? $attrs['description'] ?? null;
            $params = $attrs['params'] ?? $attrs[':params'] ?? null;
            $type   = empty(trim($innerContent)) ? 'action' : 'filter';

            // Trata ternários: $isEdit ? 'edit' : 'create'
            if (str_contains($rawName, '?') && str_contains($rawName, ':')) {
                preg_match_all('/[\'"]([a-zA-Z0-9_\.\-]+)[\'"]/', $rawName, $nameMatches);
                $names = $nameMatches[1] ?? [];
            } else {
                $names = [trim($rawName, "'\"")];
            }

            foreach ($names as $name) {
                $name = trim($name);
                if (empty($name)) {
                    continue;
                }

                if (!isset($hooks[$name])) {
                    $hooks[$name] = [
                        'name'   => $name,
                        'desc'   => $desc ? trim($desc, "'\"") : null,
                        'params' => $params ? trim($params, "'\"") : null,
                        'type'   => $type,
                        'sector' => $sector,
                        'file'   => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $filePath),
                    ];
                }
            }
        }
    }

    /**
     * Varre chamadas de doAction(...) e applyFilters(...) em arquivos PHP.
     */
    protected function scanPhpHooks(string $content, string $filePath, string $sector, array &$hooks): void
    {
        // Regex robusta: captura o comentário anterior opcional, o tipo, o nome e parâmetros
        $pattern = '/(?:(?<comment>\/\/[^\r\n]+|\/\*[\s\S]*?\*\/)\s+)?\b(?<type>doAction|applyFilters)\s*\(\s*[\'"](?<name>[a-zA-Z0-9_\.\-]+)[\'"](?<args>[^;]*?)\)\s*;(?:\s*\/\/\s*(?<inline_comment>[^\r\n]+))?/s';

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $name = $match['name'];
            $type = ($match['type'] === 'applyFilters') ? 'filter' : 'action';

            // Parâmetros (tudo após a primeira vírgula da função)
            $rawArgs = trim($match['args']);
            $rawArgs = ltrim($rawArgs, ',');
            $params  = !empty(trim($rawArgs)) ? preg_replace('/\s+/', ' ', trim($rawArgs)) : null;

            // Descrição (opcional)
            $desc = null;
            if (!empty($match['inline_comment'])) {
                // Comentário na mesma linha: doAction(...); // Descrição
                $desc = trim($match['inline_comment']);
            } elseif (!empty($match['comment'])) {
                $rawComment = trim($match['comment']);

                if (str_starts_with($rawComment, '/**')) {
                    // Limpa DocBlock
                    $lines = explode("\n", $rawComment);
                    $cleanLines = [];
                    foreach ($lines as $line) {
                        $clean = trim($line, "/* \t\r");
                        if (!empty($clean) && !str_starts_with($clean, '@')) {
                            $cleanLines[] = $clean;
                        }
                    }
                    $desc = !empty($cleanLines) ? implode(' ', $cleanLines) : null;
                } elseif (preg_match('/^\/\/\s*(?:hook(?:-desc)?\s*:\s*)?(.+)$/i', $rawComment, $cMatch)) {
                    // Comentário de barra dupla na linha anterior: // hook: Descrição ou // Descrição
                    $desc = trim($cMatch[1]);
                }
            }

            $key = "{$type}_{$name}";

            if (!isset($hooks[$key])) {
                $hooks[$key] = [
                    'name'   => $name,
                    'desc'   => $desc,
                    'params' => $params,
                    'type'   => $type,
                    'sector' => $sector,
                    'file'   => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $filePath),
                ];
            }
        }
    }
}
