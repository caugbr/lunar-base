# Plugin Maps — refatoração completa (paridade com o plugin WP `map-places`)

Reescrita da UI e ampliação de features do plugin Laravel `Maps`, com base no
plugin WordPress `map-places` como referência funcional.

## O que foi feito

### UI (edit do mapa)
- **Layout 2/3 + 1/3**: mapa grande à esquerda, painéis empilhados à direita.
- **Sincronização bidirecional** entre mapa e formulário: arrastar/zoom
  atualiza os inputs; editar os inputs move o mapa.
- **Busca de endereço embutida** na barra superior do mapa (Nominatim).
- **Marker sidebox** com estados (nenhum selecionado / editando): click num
  pin do mapa ou num chip da lista abre o editor completo à direita.
- **Chips clicáveis** com nome + cor de cada marker (ordenados por título).
- **Coordenadas ao vivo** no header do card do mapa (badge com lat, lng, zoom).
- **Parameters por marker** (key=value) com editor visual.
- **Import de markers via JSON** (upload de arquivo, mesmo formato do WP).
- **Delete-all markers**.
- **Shortcode `[map id="X"]`** com botão copiar.

### Áreas destacadas (GeoJSON)
- Select de **lugar pré-cadastrado** (arquivos em `resources/geojson/`).
- **Buscador de novos lugares** (país/estado/cidade/bairro) que consulta
  o Nominatim, exibe candidatos e salva o polygon localmente.
- **GeoJSON inline** (textarea) para colar FeatureCollections direto.
- **Estilo configurável** (color, weight, opacity, fillColor, fillOpacity).

### Front
- View pública renderiza markers + GeoJSON.
- Novo shortcode `[list-locations id="X"]` para listar os pins em cards.
- Suporte a `fullwidth` e `width` fixa.

## Instalação (Laravel)

```bash
# 1) Copie os arquivos deste pacote sobre plugins/Maps/
cp -r database Http Models Support resources routes.php  /path/to/app/plugins/Maps/

# 2) Aplique o patch do ServiceProvider (ver MapsServiceProvider-patch.md)

# 3) Rode a migration
php artisan migrate

# 4) Publique os assets
php artisan vendor:publish --tag=maps-assets --force
```

## Estrutura dos arquivos entregues

```
plugins-maps/
├── database/migrations/
│   └── 2026_07_09_000001_add_maps_extra_fields.php   # + width, fullwidth, geojson_*, uid, parameters
├── Http/Controllers/
│   ├── Admin/MapController.php                       # sync markers + import JSON + geojson_inline
│   └── Api/
│       ├── GeocodeController.php                     # busca endereços (Nominatim)
│       └── GeoJsonController.php                     # index / show / find / save
├── Models/
│   ├── Map.php                                       # + geojsonStyle(), casts
│   └── MapMarker.php                                 # + uid, parameters
├── Support/
│   └── NominatimClient.php                           # busca boundary + fetch polygon
├── resources/
│   ├── views/admin/edit.blade.php                    # UI nova (2/3 + 1/3)
│   ├── views/public/map.blade.php                    # + geojson + fullwidth
│   ├── views/public/list.blade.php                   # NOVO: [list-locations]
│   ├── assets/css/maps-admin.css                     # NOVO
│   ├── assets/css/maps.css                           # atualizado (public)
│   ├── assets/js/admin-map.js                        # NOVO (Alpine component)
│   ├── assets/js/maps.js                             # atualizado (public + geojson)
│   └── geojson/
│       ├── index.json                                # []  (populado via UI)
│       └── README.md
├── routes.php                                        # + rotas geojson
├── MapsServiceProvider-patch.md                      # trecho a adicionar no seu Provider
└── README.md                                         # este arquivo
```

## Compatibilidade

- Requer **Alpine.js 3** (carregado via CDN pelo próprio edit.blade.php).
- Requer **Leaflet 1.9+** (carregado via CDN).
- CSS usa apenas tokens já definidos em `css/admin/vars.css`
  (`--color-bg-card`, `--color-primary`, `--color-border`, `--gradient-primary`,
  `--shadow-md`, etc.) — nada hardcoded.
- Mantém o padrão `admin.layout` + `edit-box` + `admin-btn` + `form-group`
  usado por Posts/Pages.

## Formato do JSON de import de marcadores

Mesmo formato do `llegado.json` do plugin WP:

```json
[
  {
    "uid": "uid_e2twmb",
    "lat": "-22.2435906",
    "lng": "-43.7034270",
    "title": "AGFORV - Associação dos Grupos de Folias de Valença",
    "content": "Praça Visconde do Rio Preto, 126",
    "color": "#6fa5b5",
    "parameters": "categoria=cultura&telefone=99999-9999"
  }
]
```

- `uid` opcional (gerado se ausente); duplicados são ignorados no import.
- `parameters` aceita URL-encoded (string) ou objeto/array.
