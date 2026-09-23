<?php

namespace App\Support;

class ConfigTranslator
{
    protected static array $originals = [];

    /**
     * Aplica as traduções das regras definidas em config/translatable.php
     */
    public static function apply(): void
    {
        $rules = config('translatable.rules', []);

        $groupedRules = [];
        foreach ($rules as $rule) {
            $parts = explode('.', $rule, 2);
            $root = $parts[0];
            $subPath = $parts[1] ?? '';
            $groupedRules[$root][] = $subPath;
        }

        foreach ($groupedRules as $root => $subRules) {
            if (!isset(self::$originals[$root])) {
                self::$originals[$root] = config($root);
            }

            $data = self::$originals[$root];
            if (!is_array($data)) continue;

            foreach ($subRules as $subPath) {
                if (!empty($subPath)) {
                    self::applyPattern($data, explode('.', $subPath));
                }
            }

            config([$root => $data]);
        }
    }

    /**
     * Extrai termos do config/translatable.php e salva no JSON
     */
    public static function exportJson(string $locale): array
    {
        $rules = config('translatable.rules', []);
        $collectedStrings = [];

        $groupedRules = [];
        foreach ($rules as $rule) {
            $parts = explode('.', $rule, 2);
            $root = $parts[0];
            $subPath = $parts[1] ?? '';
            $groupedRules[$root][] = $subPath;
        }

        foreach ($groupedRules as $root => $subRules) {
            $data = self::$originals[$root] ?? config($root);
            if (!is_array($data)) continue;

            foreach ($subRules as $subPath) {
                if (!empty($subPath)) {
                    self::extractPattern($data, explode('.', $subPath), $collectedStrings);
                }
            }
        }

        $collectedStrings = array_unique(array_filter($collectedStrings));
        sort($collectedStrings);

        $langDir = base_path('lang');
        if (!is_dir($langDir)) mkdir($langDir, 0755, true);

        $filePath = "{$langDir}/{$locale}.json";
        $existing = file_exists($filePath) ? (json_decode(file_get_contents($filePath), true) ?: []) : [];

        $final = [];
        $addedCount = 0;

        foreach ($collectedStrings as $string) {
            if (isset($existing[$string]) && $existing[$string] !== '') {
                $final[$string] = $existing[$string];
            } else {
                $final[$string] = $existing[$string] ?? '';
                if (!isset($existing[$string])) $addedCount++;
            }
        }

        foreach ($existing as $k => $v) {
            if (!isset($final[$k])) $final[$k] = $v;
        }

        file_put_contents($filePath, json_encode($final, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return ['file' => $filePath, 'total' => count($final), 'added' => $addedCount];
    }

    protected static function applyPattern(array &$target, array $segments): void
    {
        if (empty($segments)) return;
        $segment = array_shift($segments);

        if ($segment === '*') {
            foreach ($target as &$child) {
                if (empty($segments)) {
                    if (is_string($child)) $child = __($child);
                } elseif (is_array($child)) {
                    self::applyPattern($child, $segments);
                }
            }
            unset($child);
            return;
        }

        if (isset($target[$segment])) {
            if (empty($segments)) {
                if (is_string($target[$segment])) $target[$segment] = __($target[$segment]);
            } elseif (is_array($target[$segment])) {
                self::applyPattern($target[$segment], $segments);
            }
        }
    }

    protected static function extractPattern(array $target, array $segments, array &$collected): void
    {
        if (empty($segments)) return;
        $segment = array_shift($segments);

        if ($segment === '*') {
            foreach ($target as $child) {
                if (empty($segments)) {
                    if (is_string($child) && trim($child) !== '') $collected[] = trim($child);
                } elseif (is_array($child)) {
                    self::extractPattern($child, $segments, $collected);
                }
            }
            return;
        }

        if (isset($target[$segment])) {
            if (empty($segments)) {
                if (is_string($target[$segment]) && trim($target[$segment]) !== '') $collected[] = trim($target[$segment]);
            } elseif (is_array($target[$segment])) {
                self::extractPattern($target[$segment], $segments, $collected);
            }
        }
    }
}
