<?php

namespace Plugins\Maps;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Support\Settings;
use App\Support\AdminMenu;
use App\Helpers\ContentHelper;
use Plugins\Maps\Models\Map;

class MapsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $routesFile = __DIR__ . '/routes.php';
        if (file_exists($routesFile)) {
            require $routesFile;
        }
    }

    public function boot(): void
    {
        // Carrega views com namespace "maps"
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'maps');

        // Registra o shortcode [map id="1"]
        ContentHelper::registerShortcode(
            'map',
            function ($attributes) {
                $id = $attributes['id'] ?? null;
                $slug = null;
                if (!$id) {
                    $slug = $attributes['slug'] ?? null;
                    if (!$slug) {
                        return '<!-- [map] shortcode: atributo identificador ("id" ou "slug") é obrigatório -->';
                    }
                }

                if ($slug) {
                    $map = Map::where('slug', $slug)->first();
                } else {
                    $map = Map::with('markers')->find($id);
                }

                if (!$map) return '<!-- [map] shortcode: mapa não encontrado -->';

                return view('maps::public.map', compact('map'))->render();
            },
            '',
            '[form slug="my-map"]',
            [
                'slug' =>[
                    'label'       => 'Slug registrado para o mapa',
                    'type'        => 'text',
                    'placeholder' => 'Slug do mapa',
                ],
                'id' =>[
                    'label'       => 'ID registrado para o mapa (se não há slug)',
                    'type'        => 'text',
                    'placeholder' => 'ID do mapa',
                ],
            ]
        );

        // Registra configurações no painel admin
        $this->registerSettings();

        // Adiciona link no menu lateral administrativo (padrão Lunar Base)
        AdminMenu::add([
            'label' => 'Mapas',
            'icon' => 'map',
            'route' => 'admin.maps.index',
            'active' => 'admin.maps.*',
            'permission' => 'manage-pages',
        ], 'Taxonomias');

        \App\Support\AdminMenu::addSubItem('Mapas', [
            'label' => 'Novo Mapa',
            'icon'  => 'map-plus',
            'route' => 'admin.maps.create',
            'active' => 'admin.maps.create',
            'role' => 'admin',
        ]);
    }

    protected function registerSettings(): void
    {
        // Adiciona subtítulo no grupo "general"
        Settings::add([
            'type' => 'subtitle',
            'label' => 'Mapas (OpenStreetMap)',
            'icon' => 'map',
        ], 'general');

        Settings::add([
            'type' => 'paragraph',
            'text' => 'Configurações do plugin de mapas interativos',
        ], 'general');

        // Coordenadas padrão (usadas no formulário de criação)
        Settings::add([
            'key' => 'maps_default_lat',
            'type' => 'text',
            'label' => 'Latitude padrão',
            'description' => 'Latitude central padrão para novos mapas',
            'default' => '-23.5505',
        ], 'general');

        Settings::add([
            'key' => 'maps_default_lng',
            'type' => 'text',
            'label' => 'Longitude padrão',
            'description' => 'Longitude central padrão para novos mapas',
            'default' => '-46.6333',
        ], 'general');

        Settings::add([
            'key' => 'maps_default_zoom',
            'type' => 'number',
            'label' => 'Zoom padrão',
            'description' => 'Nível de zoom padrão para novos mapas (1-18)',
            'default' => 13,
            'attributes' => ['min' => 1, 'max' => 18],
        ], 'general');

        // Configurações de tiles
        Settings::add([
            'key' => 'maps_tile_url',
            'type' => 'text',
            'label' => 'URL dos tiles',
            'description' => 'Servidor de tiles OpenStreetMap',
            'default' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        ], 'general');

        Settings::add([
            'key' => 'maps_attribution',
            'type' => 'text',
            'label' => 'Atribuição',
            'description' => 'Texto de crédito exibido no mapa',
            'default' => '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        ], 'general');
    }
}
