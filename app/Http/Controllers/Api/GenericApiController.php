<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ApiRegistry;
use App\Support\DynamicRoutes;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class GenericApiController extends Controller
{
    /**
     * Retorna as informações e configurações públicas globais do site
     * GET /api/v1/site
     */
    public function site()
    {
        $rawMenu = Config::get('site.mainMenu', []);
        $formattedMenu = [];

        foreach ($rawMenu as $item) {
            $href = null;

            if (!empty($item['route'])) {
                $href = route($item['route']);
            } elseif (!empty($item['path'])) {
                $href = url($item['path']);
            } elseif (!empty($item['slug'])) {
                $page = Page::published()->where('slug', $item['slug'])->first();
                $href = $page?->url;
            }

            if ($href) {
                $formattedMenu[] = [
                    'label' => $item['label'],
                    'url'   => $href,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'name'        => setting('general.site_name', 'Lunar Base'),
                'description' => setting('general.site_description', ''),
                'theme'       => setting('general.site_theme', 'dark'),
                'menu'        => $formattedMenu,
            ]
        ]);
    }

    /**
     * Resolve permalinks e URLs dinâmicas para aplicações SPA (Vue/Nuxt)
     * GET /api/v1/resolve?path=/blog/meu-post
     */
    public function resolve(Request $request)
    {
        $path = trim($request->input('path', ''), '/');

        if (empty($path)) {
            return $this->site();
        }

        $segments = array_values(array_filter(explode('/', $path)));
        $count = count($segments);

        // Tenta resolver no DynamicRoutes primeiro (para plugins)
        if ($dynamicData = DynamicRoutes::resolve($path)) {
            return response()->json([
                'success'  => true,
                'resolved' => true,
                'type'     => 'dynamic',
                'data'     => $dynamicData
            ]);
        }

        // Tenta resolver por Post (pelo slug do último segmento)
        $slug = end($segments);
        $post = Post::published()->where('slug', $slug)->first();
        if ($post) {
            $config = ApiRegistry::get('posts');
            return response()->json([
                'success'  => true,
                'resolved' => true,
                'type'     => 'post',
                'data'     => $this->transformItem($post, $config['schema'])
            ]);
        }

        // Tenta resolver por Página
        $page = Page::published()->where('slug', $slug)->first();
        if ($page) {
            $config = ApiRegistry::get('pages');
            return response()->json([
                'success'  => true,
                'resolved' => true,
                'type'     => 'page',
                'data'     => $this->transformItem($page, $config['schema'])
            ]);
        }

        return response()->json([
            'success'  => false,
            'message'  => 'Recurso não encontrado.',
        ], 404);
    }

    /**
     * Listagem paginada e filtrável de qualquer entidade cadastrada
     * GET /api/v1/{entity}
     */
    public function index(Request $request, string $entity)
    {
        $config = ApiRegistry::get($entity);

        if (!$config) {
            return response()->json([
                'success' => false,
                'message' => "A entidade '{$entity}' não existe ou não está exposta na API."
            ], 404);
        }

        // 1. Fonte de dados customizada (Closure) para dados que não usam Eloquent (ex: Options)
        if (isset($config['source']) && is_callable($config['source'])) {
            $rawSource = call_user_func($config['source'], $request);
            $transformed = array_map(fn($item) => $this->transformItem($item, $config['schema']), $rawSource);

            return response()->json([
                'success' => true,
                'data'    => $transformed
            ]);
        }

        // 2. Consulta padrão via Model Eloquent
        $modelClass = $config['model'];
        $query = $modelClass::query();

        // Carrega relacionamentos configurados
        if (!empty($config['with'])) {
            $query->with($config['with']);
        }

        // Aplica escopos padrão (ex: apenas publicados)
        if (isset($config['scope']) && is_callable($config['scope'])) {
            $config['scope']($query);
        }

        // Busca textual (?search=palavra)
        if ($request->filled('search') && !empty($config['searchable'])) {
            $search = $request->input('search');
            $query->where(function ($q) use ($config, $search) {
                foreach ($config['searchable'] as $field) {
                    $q->orWhere($field, 'LIKE', "%{$search}%");
                }
            });
        }

        // Paginação
        $perPage = (int) $request->input('per_page', setting('reading.posts_max_items', 15));
        $paginator = $query->paginate($perPage);

        // Transforma cada item usando o Schema
        $transformedData = collect($paginator->items())
            ->map(fn($item) => $this->transformItem($item, $config['schema']))
            ->all();

        return response()->json([
            'success' => true,
            'data'    => $transformedData,
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ]
        ]);
    }

    /**
     * Exibe um único registro de uma entidade pelo slug/id
     * GET /api/v1/{entity}/{key}
     */
    public function show(Request $request, string $entity, string $key)
    {
        $config = ApiRegistry::get($entity);

        if (!$config) {
            return response()->json(['success' => false, 'message' => 'Entidade não encontrada.'], 404);
        }

        // Caso use Closure para ler do banco/options
        if (isset($config['source_item']) && is_callable($config['source_item'])) {
            $item = call_user_func($config['source_item'], $key);
            if (!$item) {
                return response()->json(['success' => false, 'message' => 'Item não encontrado.'], 404);
            }
            return response()->json([
                'success' => true,
                'data'    => $this->transformItem($item, $config['schema'])
            ]);
        }

        // Consulta via Model Eloquent
        $modelClass = $config['model'];
        $query = $modelClass::query();

        if (!empty($config['with'])) {
            $query->with($config['with']);
        }

        if (isset($config['scope']) && is_callable($config['scope'])) {
            $config['scope']($query);
        }

        $keyColumn = $config['key_column'] ?? 'slug';
        $item = $query->where($keyColumn, $key)->first();

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Registro não encontrado.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->transformItem($item, $config['schema'])
        ]);
    }

    /**
     * Transforma um objeto/array usando a definição do Schema
     */
    // protected function transformItem(mixed $item, array $schema): array
    // {
    //     $result = [];

    //     foreach ($schema as $jsonKey => $source) {
    //         if (is_callable($source)) {
    //             // Se for Closure, executa passando o item
    //             $result[$jsonKey] = $source($item);
    //         } elseif (is_array($source)) {
    //             // Suporte a sub-schemas aninhados
    //             $result[$jsonKey] = $this->transformItem($item, $source);
    //         } else {
    //             // Suporte a notação de ponto nativa do Laravel (ex: 'author.name')
    //             $result[$jsonKey] = data_get($item, $source);
    //         }
    //     }

    //     return $result;
    // }
    protected function transformItem(mixed $item, array $schema): array
    {
        $result = [];

        foreach ($schema as $jsonKey => $source) {
            // 💡 Troque is_callable($source) por $source instanceof \Closure
            if ($source instanceof \Closure) {
                $result[$jsonKey] = $source($item);
            } elseif (is_array($source)) {
                $result[$jsonKey] = $this->transformItem($item, $source);
            } else {
                $result[$jsonKey] = data_get($item, $source);
            }
        }

        return $result;
    }
}
