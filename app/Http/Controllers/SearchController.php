<?php

namespace App\Http\Controllers;

use App\Support\PublicationTypes;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $term = trim($request->input('q', ''));

        // 1. Define quais tipos consultar:
        // Prioridade: Parâmetro da URL > Configuração do sistema > Todos os tipos registrados
        $requestedTypes = $request->input('types');
        if (empty($requestedTypes)) {
            $configuredTypes = setting('navigation.searchable_types');
            if (is_string($configuredTypes)) {
                $requestedTypes = json_decode($configuredTypes, true) ?: explode(',', $configuredTypes);
            } elseif (is_array($configuredTypes)) {
                $requestedTypes = $configuredTypes;
            }
        }

        $allTypes = PublicationTypes::all();
        $activeKeys = !empty($requestedTypes)
            ? array_intersect(array_keys($allTypes), (array) $requestedTypes)
            : array_keys($allTypes);

        $results = collect();

        // 2. Executa a busca somente se houver termo digitado
        if ($term !== '') {
            foreach ($activeKeys as $key) {
                $config = $allTypes[$key] ?? null;
                if (!$config || !class_exists($config['model'])) {
                    continue;
                }

                $modelClass = $config['model'];
                $query = $modelClass::query();

                // Eager loading com segurança
                if (!empty($config['relations'])) {
                    $query->with($config['relations']);
                }

                // Filtro dinâmico nos campos configurados
                $fields = $config['search_fields'] ?? ['title', 'content'];
                $query->where(function ($q) use ($fields, $term) {
                    foreach ($fields as $field) {
                        $q->orWhere($field, 'LIKE', "%{$term}%");
                    }
                });

                // Status 'published' (caso o model use esse padrão)
                if (\Schema::hasColumn((new $modelClass)->getTable(), 'status')) {
                    $query->where('status', 'published');
                }

                $items = $query->limit(50)->get();

                // Padroniza os itens para exibição na view
                $items->each(function ($item) use ($config) {
                    $item->_type_key = $config['key'];
                    $item->_type_label = $config['label'];
                    $item->_display_title = $item->{$config['title_field']} ?? $item->title ?? $item->name ?? 'Sem título';

                    // Excerpt de segurança
                    if (empty($item->excerpt)) {
                        $content = $item->content ?? $item->description ?? '';
                        $item->_display_excerpt = Str::limit(strip_tags($content), 160);
                    } else {
                        $item->_display_excerpt = $item->excerpt;
                    }
                });

                $results = $results->merge($items);
            }

            // Ordena por data mais recente (se houver campo de data)
            $results = $results->sortByDesc(function ($item) {
                return $item->published_at ?? $item->created_at ?? 0;
            });
        }

        // 3. Paginação manual do conjunto unificado
        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $results->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginatedResults = new LengthAwarePaginator(
            $currentItems,
            $results->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('public.search', [
            'results' => $paginatedResults,
            'term'    => $term,
            'total'   => $results->count(),
        ]);
    }
}
