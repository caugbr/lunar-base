<?php

namespace Plugins\Galleries;

use Illuminate\Support\ServiceProvider;
use App\Helpers\ContentHelper;
use App\Models\Media;
use App\Models\Post;
use App\Models\Page;

class GalleriesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 1. Views com namespace "galleries"
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'galleries');

        // 2. Registra o shortcode [gallery]
        ContentHelper::registerShortcode('gallery', function ($attributes, $content) {
            return $this->renderGallery($attributes);
        });

        // 3. Menu admin (só para a ajuda)
        // \App\Support\AdminMenu::add([
        //     'label' => 'Galleries',
        //     'icon'  => 'images',
        //     'route' => '#',
        //     'role'  => 'admin',
        // ], 'Plugins');
    }

    /**
     * Renderiza a galeria com base nos atributos do shortcode
     */
    protected function renderGallery(array $attributes): string
    {
        $attributes = array_change_key_case($attributes, CASE_LOWER);

        // 1. Descobre o post/página atual
        $model = $this->resolveCurrentModel();
        // var_dump($model);

        // 2. Monta a query de imagens
        $images = $this->resolveImages($attributes, $model);

        if ($images->isEmpty()) {
            return '';
        }

        // Define o layout (padrão: grid)
        $layout = in_array($attributes['layout'] ?? '', ['grid', 'masonry', 'carousel'])
            ? $attributes['layout']
            : 'grid';

        // 3. Configurações visuais
        $columns   = max(1, min(6, (int) ($attributes['columns'] ?? $attributes['cols'] ?? 3)));
        $size      = in_array($attributes['size'] ?? '', ['thumb', 'medium', 'large', 'original'])
            ? $attributes['size']
            : 'medium';
        $lightbox  = ($attributes['lightbox'] ?? 'true') !== 'false';
        $caption   = ($attributes['caption'] ?? 'true') !== 'false';
        $gap       = max(0, (int) ($attributes['gap'] ?? 8));
        $ratio     = $attributes['ratio'] ?? 'square'; // square, auto, 4/3, 16/9
        $rounded   = ($attributes['rounded'] ?? 'true') !== 'false';

        return view('galleries::public.gallery', [
            'layout'   => $layout,
            'images'   => $images,
            'columns'  => $columns,
            'size'     => $size,
            'lightbox' => $lightbox,
            'caption'  => $caption,
            'gap'      => $gap,
            'ratio'    => $ratio,
            'rounded'  => $rounded,
        ])->render();
    }

    /**
     * Tenta descobrir o Post/Page atual via rota, view compartilhada ou fallback
     */
    // protected function resolveCurrentModel()
    // {
    //     // Tenta via rota (show do post/page)
    //     $route = request()->route();
    //     // print "route<pre>";
    //     // print_r($route); die;
    //     if ($route) {
    //         foreach (['post', 'page'] as $param) {
    //             $value = $route->parameter($param);
    //             if ($value instanceof \Illuminate\Database\Eloquent\Model) {
    //                 return $value;
    //             }
    //         }
    //     }

    //     // Tenta via view compartilhada
    //     foreach (['post', 'page'] as $var) {
    //         if (view()->shared($var)) {
    //             return view()->shared($var);
    //         }
    //     }

    //     return null;
    // }
    /**
     * Tenta descobrir o Post/Page atual de forma robusta (por tipo, não por nome)
     */
    protected function resolveCurrentModel()
    {
        // 1. Verifica TODOS os parâmetros da rota atual
        $route = request()->route();
        if ($route) {
            foreach ($route->parameters() as $param) {
                if ($param instanceof \App\Models\Post || $param instanceof \App\Models\Page) {
                    return $param;
                }
            }
        }

        // 2. Verifica TODAS as variáveis compartilhadas com a view
        $shared = view()->getShared();
        foreach ($shared as $value) {
            if ($value instanceof \App\Models\Post || $value instanceof \App\Models\Page) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Resolve as imagens conforme os atributos
     */
    protected function resolveImages(array $attributes, $model)
    {
        // Modo 1: IDs explícitos — [gallery ids="1,2,3"]
        if (!empty($attributes['ids'])) {
            $ids = array_filter(array_map('intval', explode(',', $attributes['ids'])));
            if (empty($ids)) {
                return collect();
            }
            return Media::whereIn('id', $ids)
                ->images()
                ->get()
                ->sortBy(function ($m) use ($ids) {
                    return array_search($m->id, $ids);
                })->values();
        }

        // Modo 2: Imagens vinculadas ao model atual
        if (!$model) {
            return collect();
        }

        $query = Media::where('mediaable_id', $model->id)
            ->where('mediaable_type', get_class($model))
            ->images();

        // Excluir thumbnail do post (padrão: sim)
        $excludeThumb = ($attributes['exclude_thumbnail'] ?? 'true') !== 'false';
        if ($excludeThumb && !empty($model->thumbnail_id)) {
            $query->where('id', '!=', $model->thumbnail_id);
        }

        // Ordenação
        $orderBy = in_array($attributes['orderby'] ?? '', ['id', 'name', 'created_at'])
            ? $attributes['orderby']
            : 'created_at';
        $order = in_array(strtolower($attributes['order'] ?? ''), ['asc', 'desc', 'rand'])
            ? strtolower($attributes['order'])
            : 'asc';

        if ($order === 'rand') {
            $query->inRandomOrder();
        } else {
            $query->orderBy($orderBy, $order);
        }

        // Limite
        if (!empty($attributes['limit'])) {
            $query->limit(max(1, (int) $attributes['limit']));
        }

        return $query->get();
    }
}
