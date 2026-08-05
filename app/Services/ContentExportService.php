<?php

namespace App\Services;

use App\Support\PublicationTypes;
use App\Models\Taxonomy;
use Illuminate\Support\Facades\File;
use Exception;

class ContentExportService
{
    /**
     * Gera o arquivo JSON contendo os tipos de publicação e taxonomias selecionados
     *
     * @param array $selectedTypes Array de chaves selecionadas (ex: ['post', 'page', 'taxonomies'])
     * @return string Caminho completo do arquivo JSON gerado
     */
    public function generateExport(array $selectedTypes): string
    {
        if (empty($selectedTypes)) {
            throw new Exception("Selecione ao menos um tipo de conteúdo para exportar.");
        }

        $exportData = [
            'manifest' => [
                'app_name'     => setting('general.site_name', 'Lunar Base'),
                'app_version'  => config('app.version', '1.3.0'),
                'exported_at'  => now()->toIso8601String(),
                'types'        => $selectedTypes,
            ],
            'taxonomies' => [],
            'content'    => [],
        ];

        // 1. Exporta Taxonomias e seus Termos (se selecionado)
        if (in_array('taxonomies', $selectedTypes, true)) {
            $exportData['taxonomies'] = Taxonomy::with('terms')->get()->toArray();
        }

        // 2. Exporta cada Tipo de Publicação registrado (Posts, Páginas, Cursos, etc.)
        $allTypes = PublicationTypes::all();

        foreach ($selectedTypes as $typeKey) {
            if ($typeKey === 'taxonomies') continue;

            if (isset($allTypes[$typeKey])) {
                $typeInfo   = $allTypes[$typeKey];
                $modelClass = $typeInfo['model'];
                $relations  = $typeInfo['relations'] ?? [];

                if ($modelClass && class_exists($modelClass)) {
                    $exportData['content'][$typeKey] = $modelClass::with($relations)->get()->toArray();
                }
            }
        }

        // 3. Salva o arquivo JSON na pasta temporária de storage
        $filename = 'lunar-content-export-' . date('Y-m-d_H-i-s') . '.json';
        $exportPath = storage_path('app/temp/' . $filename);

        File::ensureDirectoryExists(storage_path('app/temp'));
        File::put($exportPath, json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $exportPath;
    }
}
