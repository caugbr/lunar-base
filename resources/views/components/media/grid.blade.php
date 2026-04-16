@props([
    'id' => 'mediaGrid-' . Str::random(6),
    'selectable' => false,
    'context' => null,
    'multiple' => true,
    'onSelect' => null,
    'perPage' => 20,
    'initialLinked' => '',
    'initialType' => '',
    'csrfToken' => csrf_token(),
    'mediaable' => null,
])

<div
    x-data="mediaGridComponent({
        id: '{{ $id }}',
        selectable: {{ $selectable ? 'true' : 'false' }},
        multiple: {{ $multiple ? 'true' : 'false' }},
        onSelect: {{ $onSelect ?: 'null' }},
        perPage: {{ $perPage }},
        initialType: '{{ $initialType }}',
        initialLinked: '{{ $initialLinked }}',
        csrfToken: '{{ $csrfToken }}'
    })"
    x-init="init()"
    @media:updated.window="loadData()"
    {{ $attributes->merge(['class' => 'media-grid-container']) }}
>
    {{-- Filtros --}}
    <div class="admin-filters">
        <div class="admin-filters-row">
            <div class="admin-filter-group">
                <select x-model="filters.linked" @change="loadData()" class="admin-filter-select">
                    <option value="all">Todas</option>
                    <option value="orphan">Órfãos</option>
                    <option value="linked">Vinculadas</option>
                </select>
            </div>
            <div class="admin-filter-group">
                <select x-model="filters.type" @change="loadData()" class="admin-filter-select">
                    <option value="">Todos os tipos</option>
                    <option value="image">Imagens</option>
                    <option value="document">Documentos</option>
                </select>
            </div>
            <div class="admin-filter-group" style="flex: 2;">
                <input type="text"
                       x-model.debounce.500ms="filters.search"
                       @input="loadData()"
                       class="admin-filter-input"
                       placeholder="Buscar por nome ou descrição...">
            </div>
        </div>
    </div>

    {{-- Loading / Vazio --}}
    <div x-show="loading" class="admin-text-center admin-text-muted" style="padding: 2rem;">
        <x-lucide-loader class="lucid-icon animate-spin" /> Carregando mídia...
    </div>

    <div x-show="!loading && media.length === 0" class="admin-text-center admin-text-muted" style="padding: 2rem;">
        Nenhuma mídia encontrada.
    </div>

    {{-- Grid --}}
    <div class="media-grid" x-show="!loading && media.length > 0">
        <template x-for="item in media" :key="item.id">
            <div class="media-card" :class="{ 'is-image': item.is_image, 'selectable': selectable }">

                {{-- Checkbox de seleção (apenas em modo selectable) --}}
                <template x-if="selectable">
                    <label class="media-select">
                        <input type="checkbox"
                               x-model="selectedIds"
                               :value="item.id"
                               @click.stop>
                    </label>
                </template>

                {{-- Thumbnail --}}
                <div class="media-thumb" @click="selectable ? toggleSelection(item.id) : null">
                    <template x-if="item.is_image">
                        <img :src="item.thumbnail_url" :alt="item.alt || item.name" loading="lazy">
                    </template>
                    <template x-if="!item.is_image">
                        <div class="media-icon-placeholder">
                            <x-lucide-file-text class="lucid-icon" />
                        </div>
                    </template>
                </div>

                {{-- Info --}}
                <div class="media-info">
                    <h4 x-text="item.name" class="media-name" :title="item.name"></h4>
                    <div class="meta">
                        <span x-text="item.size_formatted"></span>
                        <span x-text="item.created_at"></span>
                    </div>

                    {{-- Ações: modo gerenciamento (edit/delete) --}}
                    <template x-if="!selectable">
                        <div class="media-actions">
                            <button @click="editItem(item)" class="admin-btn admin-btn-secondary">
                                <x-lucide-pencil class="lucid-icon" /> Editar
                            </button>
                            <button @click="deleteItem(item)" class="admin-btn admin-btn-danger">
                                <x-lucide-trash-2 class="lucid-icon" /> Excluir
                            </button>
                        </div>
                    </template>

                    {{-- Ação: modo seleção (inserir) --}}
                    <template x-if="selectable && isSelected(item.id)">
                        <button @click="insertSelected([item])" class="admin-btn admin-btn-primary">
                            <x-lucide-check class="lucid-icon" /> Inserir
                        </button>
                    </template>
                </div>
            </div>
        </template>
    </div>

    {{-- Paginação --}}
    <div x-show="!loading && pagination.last_page > 1" class="admin-pagination">
        <div class="pagination-desktop">
            <div class="pagination-info">
                Página <span x-text="pagination.current_page"></span> de <span x-text="pagination.last_page"></span>
            </div>
            <div class="pagination-links">
                <button @click="goToPage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1">←</button>
                <template x-for="page in pagination.pagesInRange" :key="page">
                    <button @click="goToPage(page)"
                            :class="{ 'active': page === pagination.current_page }"
                            x-text="page"></button>
                </template>
                <button @click="goToPage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page">→</button>
            </div>
        </div>
    </div>

    {{-- Barra de seleção múltipla (apenas modo selectable) --}}
    <div x-show="selectable && multiple && selectedIds.length > 0" class="media-selection-bar">
        <span x-text="selectedIds.length + ' item(ns) selecionado(s)'"></span>
        <button @click="insertSelected()" class="admin-btn admin-btn-primary">Inserir selecionados</button>
        <button @click="clearSelection()" class="admin-btn admin-btn-secondary">Limpar</button>
    </div>

    @push('styles')
    <style>
        .media-grid-container { /* Escopo isolado para o componente */ }
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .media-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            overflow: hidden;
            transition: box-shadow 0.2s;
            position: relative;
        }
        .media-card.selectable { cursor: pointer; }
        .media-card.selectable:hover, .media-card.selected {
            box-shadow: 0 0 0 2px #718096;
        }
        .media-select {
            position: absolute;
            top: 0.5rem;
            left: 0.5rem;
            z-index: 2;
            background: white;
            border-radius: 9999px;
            padding: 0.25rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .media-thumb {
            height: 140px;
            background: #f9fafb;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-bottom: 1px solid #e5e7eb;
        }
        .media-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .media-icon-placeholder { color: #9ca3af; }
        .media-icon-placeholder .lucid-icon { width: 32px; height: 32px; }
        .media-info { padding: 0.75rem; }
        .media-name {
            font-size: 0.85rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .meta {
            font-size: 0.7rem;
            color: #6b7280;
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }
        .media-actions { display: flex; gap: 0.5rem; }
        .media-actions button { padding: 4px 8px; font-size: 0.8rem; }

        .media-selection-bar {
            position: fixed;
            bottom: 1rem;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 100;
        }

        .pagination-links button {
            min-width: 2rem;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            background-color: white;
            color: #374151;
            transition: all 0.2s;
        }
        .pagination-links button:hover { background-color: #f3f4f6; }
        .pagination-links button.active {
            background-color: #718096;
            border-color: #575d64;
            color: white;
        }
        .pagination-links button:disabled { opacity: 0.5; cursor: not-allowed; }
    </style>
    @endpush

    @push('scripts')
    <script>
    function mediaGridComponent(config) {
        return {
            // Estado
            media: [],
            loading: false,
            selectedIds: [],
            currentContext: config.context || 'grid', // Padrão 'grid'

            // Config
            id: config.id,
            selectable: config.selectable,
            multiple: config.multiple,
            onSelect: config.onSelect,
            perPage: config.perPage,
            csrfToken: config.csrfToken,

            // Filtros e paginação
            filters: {
                type: config.initialType,
                search: '',
                linked: config.initialLinked || 'all' // 'all' | 'orphan' | 'linked'
            },
            pagination: { current_page: 1, last_page: 1, pagesInRange: [] },

            // Inicialização
            init() {
                this.loadData();

                // Escuta o modal abrir para definir o contexto
                window.addEventListener('modal-open', (e) => {
                    // console.log('init', e.detail?.context)
                    this.currentContext = e.detail?.context || 'grid';
                });
            },

            // Carregar dados via AJAX
            async loadData() {
                this.loading = true;
                try {
                    const params = new URLSearchParams({
                        linked: this.filters.linked,
                        type: this.filters.type,
                        search: this.filters.search,
                        page: this.pagination.current_page,
                        per_page: this.perPage,
                        _t: Date.now()
                    });

                    // 👈 Se tiver mediaable, adiciona type/id para o filtro "linked"
                    @if($mediaable)
                        params.append('mediaable_type', '{{ get_class($mediaable) }}');
                        params.append('mediaable_id', '{{ $mediaable->id }}');
                    @endif

                    const response = await fetch(`/admin/media/data?${params}`, {
                        headers: { 'Accept': 'application/json' }
                    });

                    const data = await response.json();
                    this.media = data.data;
                    this.pagination.current_page = data.current_page;
                    this.pagination.last_page = data.last_page;
                    this.generatePages();

                } catch (err) {
                    console.error('Erro ao carregar mídia:', err);
                } finally {
                    this.loading = false;
                }
            },

            // Paginação
            goToPage(page) {
                if (page >= 1 && page <= this.pagination.last_page) {
                    this.pagination.current_page = page;
                    this.loadData();
                }
            },

            generatePages() {
                const range = [];
                const current = this.pagination.current_page;
                const last = this.pagination.last_page;
                let start = Math.max(1, current - 2);
                let end = Math.min(last, current + 2);

                if (current <= 3) end = Math.min(5, last);
                if (current >= last - 2) start = Math.max(1, last - 4);

                for (let i = start; i <= end; i++) range.push(i);
                this.pagination.pagesInRange = range;
            },

            // Seleção
            toggleSelection(id) {
                if (!this.selectable) return;

                const index = this.selectedIds.indexOf(id);

                if (index === -1) {
                    // Se não permite múltiplo, limpa seleção anterior primeiro
                    if (!this.multiple) {
                        this.selectedIds = [];
                    }
                    this.selectedIds.push(id);
                } else {
                    // Permite desmarcar mesmo em modo único
                    this.selectedIds.splice(index, 1);
                }
            },

            isSelected(id) {
                return this.selectedIds.includes(id);
            },

            clearSelection() {
                this.selectedIds = [];
            },

            // Inserir selecionados (callback ou evento)
            insertSelected(items = null) {
                const selected = items || this.media.filter(m => this.selectedIds.includes(m.id));
                if (!selected.length) return;

                if (typeof this.onSelect === 'function') {
                    // Callback direto (Alpine)
                    this.onSelect(selected.length === 1 ? selected[0] : selected);
                } else {
                    // Evento global (JS vanilla)
                    window.dispatchEvent(new CustomEvent('media:inserted', {
                        detail: {
                            media: selected.length === 1 ? selected[0] : selected,
                            source: this.currentContext
                        }
                    }));
                }

                this.clearSelection();
            },

            // Ações (modo gerenciamento)
            editItem(item) {
                if (this.selectable) return;
                window.dispatchEvent(new CustomEvent('media:edit', { detail: { item } }));
            },

            async deleteItem(item) {
                if (this.selectable) return;
                if (!confirm(`Excluir "${item.name}" permanentemente?`)) return;

                try {
                    const response = await fetch(`/admin/media/${item.id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken
                        }
                    });

                    if (response.ok) {
                        this.media = this.media.filter(m => m.id !== item.id);
                        if (this.media.length === 0 && this.pagination.current_page > 1) {
                            this.pagination.current_page--;
                            this.loadData();
                        } else {
                            window.dispatchEvent(new CustomEvent('media:updated'));
                        }
                    }
                } catch (err) {
                    console.error('Erro ao excluir:', err);
                    alert('Erro ao excluir mídia.');
                }
            }
        }
    }
    </script>
    @endpush
</div>
