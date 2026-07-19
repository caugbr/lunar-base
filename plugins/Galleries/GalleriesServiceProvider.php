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
        // ContentHelper::registerShortcode('gallery', function ($attributes, $content) {
        //     return $this->renderGallery($attributes);
        // });
        // 2. Registra o shortcode [gallery] com a documentação e o esquema de atributos completo
        ContentHelper::registerShortcode(
            'gallery',
            function ($attributes, $content) {
                return $this->renderGallery($attributes);
            },
            'Renderiza uma galeria de fotos dinâmica (a partir de IDs específicos ou vinculadas ao post atual).',
            '[gallery layout="grid" columns="3" size="medium" lightbox="true" ratio="square"]',
            [
                'ids' => [
                    'label'       => 'IDs das Imagens',
                    'type'        => 'text',
                    'placeholder' => 'Ex: 12,15,48 (Deixe em branco para usar fotos do post)',
                ],
                'layout' => [
                    'label'   => 'Layout da Galeria',
                    'type'    => 'select',
                    'options' => [
                        'grid'     => 'Grade (Grid)',
                        'masonry'  => 'Mosaico (Masonry)',
                        'carousel' => 'Carrossel (Slider)'
                    ],
                    'default' => 'grid'
                ],
                'columns' => [
                    'label'   => 'Colunas (Grades e Mosaicos)',
                    'type'    => 'select',
                    'options' => [
                        '1' => '1 Coluna',
                        '2' => '2 Colunas',
                        '3' => '3 Colunas',
                        '4' => '4 Colunas',
                        '5' => '5 Colunas',
                        '6' => '6 Colunas'
                    ],
                    'default' => '3'
                ],
                'size' => [
                    'label'   => 'Tamanho das Imagens',
                    'type'    => 'select',
                    'options' => [
                        'thumb'    => 'Miniatura (Thumb)',
                        'medium'   => 'Médio (Medium)',
                        'large'    => 'Grande (Large)',
                        'original' => 'Tamanho Original (Full)'
                    ],
                    'default' => 'medium'
                ],
                'ratio' => [
                    'label'   => 'Proporção do Corte (Aspect Ratio)',
                    'type'    => 'select',
                    'options' => [
                        'square' => 'Quadrado (1:1)',
                        '4/3'    => 'Proporção 4:3',
                        '16/9'   => 'Proporção 16:9',
                        'auto'   => 'Automático (Manter proporção original)'
                    ],
                    'default' => 'square'
                ],
                'gap' => [
                    'label'       => 'Espaçamento das Fotos (em px)',
                    'type'        => 'number',
                    'placeholder' => 'Ex: 8',
                    'default'     => '8'
                ],
                'lightbox' => [
                    'label'   => 'Ativar efeito Lightbox (Ampliar ao clicar)',
                    'type'    => 'select',
                    'options' => [
                        'true'  => 'Sim',
                        'false' => 'Não'
                    ],
                    'default' => 'true'
                ],
                'caption' => [
                    'label'   => 'Exibir legenda das fotos',
                    'type'    => 'select',
                    'options' => [
                        'true'  => 'Sim',
                        'false' => 'Não'
                    ],
                    'default' => 'true'
                ],
                'rounded' => [
                    'label'   => 'Cantos arredondados nas imagens',
                    'type'    => 'select',
                    'options' => [
                        'true'  => 'Sim',
                        'false' => 'Não'
                    ],
                    'default' => 'true'
                ],
                'exclude_thumbnail' => [
                    'label'   => 'Excluir imagem destacada (Capa do post)',
                    'type'    => 'select',
                    'options' => [
                        'true'  => 'Sim',
                        'false' => 'Não'
                    ],
                    'default' => 'true'
                ],
                'orderby' => [
                    'label'   => 'Ordenar fotos por',
                    'type'    => 'select',
                    'options' => [
                        'created_at' => 'Data de Cadastro',
                        'name'       => 'Nome do Arquivo',
                        'id'         => 'ID da Mídia'
                    ],
                    'default' => 'created_at'
                ],
                'order' => [
                    'label'   => 'Direção da ordenação',
                    'type'    => 'select',
                    'options' => [
                        'asc'  => 'Crescente (ASC)',
                        'desc' => 'Decrescente (DESC)',
                        'rand' => 'Aleatório (RAND)'
                    ],
                    'default' => 'asc'
                ],
                'limit' => [
                    'label'       => 'Limite de fotos (Opcional)',
                    'type'        => 'number',
                    'placeholder' => 'Vazio para ilimitado'
                ]
            ]
        );
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
