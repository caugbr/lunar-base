# Patch para MapsServiceProvider.php

Adicione estes trechos ao seu `plugins/Maps/MapsServiceProvider.php` existente.
Não substitua o arquivo inteiro — só complete o que estiver faltando.

## 1. No `boot()`, publique os assets do plugin

```php
public function boot(): void
{
    // Views namespaced (você provavelmente já faz isso)
    $this->loadViewsFrom(__DIR__ . '/resources/views', 'maps');

    // Rotas
    $this->loadRoutesFrom(__DIR__ . '/routes.php');

    // Migrations
    $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

    // ─── Assets públicos (CSS/JS) ─────────────────────────────
    // Copia para public/plugins/maps/ quando rodar php artisan vendor:publish
    $this->publishes([
        __DIR__ . '/resources/assets' => public_path('plugins/maps'),
    ], 'maps-assets');

    // Shortcodes — depende do seu ShortcodeService.
    // Ajuste o nome do binding conforme sua implementação.
    if ($this->app->bound('shortcodes')) {
        $this->app['shortcodes']->register('map', function ($attrs) {
            $id = (int) ($attrs['id'] ?? 0);
            $map = \Plugins\Maps\Models\Map::with('markers')->find($id);
            if (!$map) return '';
            return view('maps::public.map', compact('map'))->render();
        });

        $this->app['shortcodes']->register('list-locations', function ($attrs) {
            $id = (int) ($attrs['id'] ?? 0);
            $map = \Plugins\Maps\Models\Map::with('markers')->find($id);
            if (!$map) return '';
            return view('maps::public.list', compact('map'))->render();
        });
    }
}
```

## 2. Publique os assets uma vez após instalar

```bash
php artisan vendor:publish --tag=maps-assets --force
```

Isso cria:
```
public/plugins/maps/css/maps.css
public/plugins/maps/css/maps-admin.css
public/plugins/maps/js/maps.js
public/plugins/maps/js/admin-map.js
```

## 3. Se você não usa um sistema de shortcodes ainda

Registre um filtro no seu `App\Support\Content` (ou onde processa `$post->content`)
substituindo os padrões `[map id="X"]` e `[list-locations id="X"]` pelas views
acima. Exemplo minimalista:

```php
public static function renderShortcodes(string $html): string
{
    return preg_replace_callback('/\[map\s+id="(\d+)"\]/', function ($m) {
        $map = \Plugins\Maps\Models\Map::with('markers')->find((int) $m[1]);
        return $map ? view('maps::public.map', compact('map'))->render() : '';
    }, $html);
}
```
