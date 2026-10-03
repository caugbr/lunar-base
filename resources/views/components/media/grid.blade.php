@props([
    'id' => 'mediaGrid-' . Str::random(6),
    'selectable' => true,
    'context' => null,
    'multiple' => false,
    'onSelect' => null,
    'perPage' => 20,
    'initialLinked' => '',
    'initialType' => '',
    'csrfToken' => csrf_token(),
    'mediaable' => null,
    'selected' => '',
    'buttonLabel' => 'Inserir',
    'bulkButtonLabel' => 'Inserir selecionados',
    'clearButtonLabel' => 'Limpar'
])

@php
    $selected = is_string($selected) && !empty(trim($selected))
        ? array_map('intval', explode(',', $selected))
        : (is_array($selected) ? $selected : []);
@endphp

<div
    x-data="mediaGridComponent({
        id: '{{ $id }}',
        selectable: {{ $selectable ? 'true' : 'false' }},
        multiple: {{ $multiple ? 'true' : 'false' }},
        onSelect: {{ $onSelect ?: 'null' }},
        perPage: {{ $perPage }},
        initialType: '{{ $initialType }}',
        initialLinked: '{{ $initialLinked }}',
        csrfToken: '{{ $csrfToken }}',
        selected: @json($selected),
        buttonLabel: '{{ $buttonLabel }}',
        bulkButtonLabel: '{{ $bulkButtonLabel }}',
        clearButtonLabel: '{{ $clearButtonLabel }}'
    })"
    x-init="init()"
    @media:updated.window="loadData()"
    @media:set-multiple.window="multiple = $event.detail.multiple;"
    @media:set-selected.window="selectedIds = Array.isArray($event.detail.selected) ? [...$event.detail.selected] : [$event.detail.selected].filter(Boolean);"
    @media:set-labels.window="buttonLabel = $event.detail.labels.insert; bulkButtonLabel = $event.detail.labels.bulkInsert; clearButtonLabel = $event.detail.labels.clear;"
    @media:set-options.window="
        allowSizeSelect = $event.detail.allowSizeSelect !== undefined ? $event.detail.allowSizeSelect : true;
        fixedSize = $event.detail.fixedSize || null;
    "
    {{ $attributes->merge(['class' => 'media-grid-container']) }}
>
    <div class="media-workspace">
        {{-- COLUNA PRINCIPAL: FILTROS + GRID --}}
        <div class="media-main-col">
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
                    <div class="media-card"
                         :class="{
                             'is-image': item.is_image,
                             'selectable': selectable,
                             'selected': isSelected(item.id),
                             'active-inspect': activeItem?.id === item.id
                         }">

                        {{-- Checkbox de seleção múltipla --}}
                        <template x-if="multiple && selectable">
                            <label class="media-select">
                                <input type="checkbox"
                                       x-model="selectedIds"
                                       :value="item.id"
                                       @click.stop>
                            </label>
                        </template>

                        {{-- Thumbnail --}}
                        <div class="media-thumb" @click="handleCardClick(item)">
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

                            {{-- Ações: modo standalone (edit/delete) --}}
                            <template x-if="!selectable">
                                <div class="media-actions">
                                    <button @click="editItem(item)" class="admin-btn admin-btn-secondary" title="Editar">
                                        <x-lucide-pencil class="lucid-icon" /> Editar
                                    </button>
                                    <button @click="deleteItem(item)" class="admin-btn admin-btn-danger" title="Excluir">
                                        <x-lucide-trash-2 class="lucid-icon" />
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Paginação --}}
            {{-- <div x-show="!loading && pagination.last_page > 1" class="admin-pagination">
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
            </div> --}}
            {{-- Paginação no padrão nativo da Administração --}}
            <div x-show="!loading && pagination.last_page > 1" class="admin-pagination">
                {{-- Versão Mobile --}}
                <div class="pagination-mobile">
                    <template x-if="pagination.current_page <= 1">
                        <span class="pagination-prev disabled">← Anterior</span>
                    </template>
                    <template x-if="pagination.current_page > 1">
                        <a href="#" @click.prevent="goToPage(pagination.current_page - 1)" class="pagination-prev">← Anterior</a>
                    </template>

                    <template x-if="pagination.current_page >= pagination.last_page">
                        <span class="pagination-next disabled">Próximo →</span>
                    </template>
                    <template x-if="pagination.current_page < pagination.last_page">
                        <a href="#" @click.prevent="goToPage(pagination.current_page + 1)" class="pagination-next">Próximo →</a>
                    </template>
                </div>

                {{-- Versão Desktop --}}
                <div class="pagination-desktop">
                    <div class="pagination-info">
                        Página <span x-text="pagination.current_page"></span> de <span x-text="pagination.last_page"></span>
                    </div>

                    <div class="pagination-links">
                        {{-- Botão Anterior (←) --}}
                        <template x-if="pagination.current_page <= 1">
                            <span class="disabled">←</span>
                        </template>
                        <template x-if="pagination.current_page > 1">
                            <a href="#" @click.prevent="goToPage(pagination.current_page - 1)">←</a>
                        </template>

                        {{-- Números das Páginas --}}
                        <template x-for="page in pagination.pagesInRange" :key="page">
                            <span style="padding: 0; border: 0; margin: 0;">
                                <template x-if="page === pagination.current_page">
                                    <span class="active" x-text="page"></span>
                                </template>
                                <template x-if="page !== pagination.current_page">
                                    <a href="#" @click.prevent="goToPage(page)" x-text="page"></a>
                                </template>
                            </span>
                        </template>

                        {{-- Botão Próximo (→) --}}
                        <template x-if="pagination.current_page >= pagination.last_page">
                            <span class="disabled">→</span>
                        </template>
                        <template x-if="pagination.current_page < pagination.last_page">
                            <a href="#" @click.prevent="goToPage(pagination.current_page + 1)" rel="next">→</a>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- GAVETA LATERAL: CONFIGURAÇÕES DA IMAGEM ANTES DE INSERIR --}}
        <template x-if="selectable && !multiple && activeItem">
            <div class="media-sidebar-inspect">
                <div class="inspect-header">
                    <h4>Detalhes da Inserção</h4>
                    <button type="button" @click="activeItem = null; selectedIds = []" class="transparent-btn">
                        <x-lucide-x class="lucid-icon" />
                    </button>
                </div>

                <div class="inspect-preview">
                    <img :src="activeItem.thumbnail_url" :alt="activeItem.alt">
                    <div class="inspect-meta">
                        <strong x-text="activeItem.name"></strong>
                        <span x-text="activeItem.size_formatted"></span>
                    </div>
                </div>

                <div class="inspect-fields">
                    {{-- SELETOR DE TAMANHOS (O PULO DO GATO) --}}
                    <div class="form-group" x-show="allowSizeSelect && activeItem.is_image && !activeItem.is_svg">
                        <label for="inspect-variant">Tamanho da Imagem:</label>
                        <select id="inspect-variant" x-model="selectedVariant" class="form-input">
                            <template x-for="(variant, key) in activeItem.variants" :key="key">
                                <option :value="key" x-text="variant.label"></option>
                            </template>
                        </select>
                        <small>Escolha o tamanho otimizado para o seu layout.</small>
                    </div>

                    {{-- AVISO SVG --}}
                    <div class="form-group" x-show="activeItem.is_svg">
                        <small class="admin-badge admin-badge-neutral">Vetor SVG: Renderiza em escala perfeita sem perda de qualidade.</small>
                    </div>

                    <div class="form-group">
                        <label for="inspect-alt">Texto Alternativo (Alt):</label>
                        <input type="text" id="inspect-alt" x-model="activeItem.alt" class="form-input" placeholder="Descrição da imagem para acessibilidade">
                    </div>

                    <div class="form-group">
                        <label for="inspect-caption">Legenda:</label>
                        <textarea id="inspect-caption" x-model="activeItem.caption" rows="2" class="form-input" placeholder="Legenda opcional"></textarea>
                    </div>

                    <div class="inspect-actions">
                        <button type="button" @click="insertCurrent()" class="admin-btn admin-btn-primary full-width">
                            <x-lucide-check class="lucid-icon" />
                            <span x-text="buttonLabel"></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Barra de seleção múltipla (apenas modo selectable + multiple) --}}
    <div x-show="selectable && multiple && selectedIds.length > 0" class="media-selection-bar">
        <span x-text="'Selecionados: ' + selectedIds.length"></span>
        <button @click="insertBulk()" class="admin-btn admin-btn-primary">
            <x-lucide-check class="lucid-icon" />
            <span x-text="bulkButtonLabel"></span>
        </button>
        <button @click="clearSelection()" class="admin-btn admin-btn-secondary">
            <span x-text="clearButtonLabel"></span>
        </button>
    </div>
</div>

@push('styles')
<style>
    .media-workspace {
        display: flex;
        gap: 1.5rem;
        align-items: flex-start;
        position: relative;
    }
    .media-main-col {
        flex: 1;
        min-width: 0;
    }
    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .media-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        overflow: hidden;
        transition: all 0.2s ease;
        position: relative;
    }
    .media-card.selectable { cursor: pointer; }
    .media-card.selectable:hover {
        box-shadow: 0 0 0 2px #cbd5e1;
    }
    .media-card.selected, .media-card.active-inspect {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px #3b82f6;
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
        height: 120px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-bottom: 1px solid #e2e8f0;
    }
    .media-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .media-icon-placeholder { color: #94a3b8; }
    .media-icon-placeholder .lucid-icon { width: 32px; height: 32px; }
    .media-info { padding: 0.6rem; }
    .media-name {
        font-size: 0.8rem;
        font-weight: 500;
        color: #334155;
        margin-bottom: 0.2rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .meta {
        font-size: 0.68rem;
        color: #64748b;
        display: flex;
        justify-content: space-between;
    }
    .media-actions { display: flex; gap: 0.4rem; margin-top: 0.5rem; }
    .media-actions button { padding: 2px 6px; font-size: 0.75rem; }

    /* GAVETA LATERAL DE DETALHES */
    .media-sidebar-inspect {
        width: 280px;
        flex-shrink: 0;
        background: #ffffff;
        border: 1px solid var(--color-border, #e2e8f0);
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        position: sticky;
        top: 0;
    }
    .inspect-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--color-border, #e2e8f0);
    }
    .inspect-header h4 { margin: 0; font-size: 0.9rem; font-weight: 600; }
    .inspect-preview {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .inspect-preview img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
    }
    .inspect-meta {
        font-size: 0.75rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        overflow: hidden;
    }
    .inspect-meta strong {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .inspect-fields .form-group {
        margin-bottom: 0.75rem;
    }
    .inspect-fields label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    .inspect-fields .form-input {
        width: 100%;
        font-size: 0.8rem;
        padding: 0.35rem 0.5rem;
    }
    .inspect-actions {
        margin-top: 1rem;
    }
    .full-width {
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
    }

    .media-selection-bar {
        position: fixed;
        bottom: 1.5rem;
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
</style>
@endpush

@push('scripts')
<script>
function mediaGridComponent(config) {
    return {
        media: [],
        loading: false,
        selectedIds: [],
        activeItem: null,
        selectedVariant: 'original',
        allowSizeSelect: true,
        fixedSize: null,
        currentContext: config.context || 'grid',
        modalOpenDetail: {},

        id: config.id,
        selectable: config.selectable,
        multiple: config.multiple,
        onSelect: config.onSelect,
        perPage: config.perPage,
        csrfToken: config.csrfToken,
        selected: config.selected,
        buttonLabel: config.buttonLabel,
        bulkButtonLabel: config.bulkButtonLabel,
        clearButtonLabel: config.clearButtonLabel,

        filters: {
            type: config.initialType,
            search: '',
            linked: config.initialLinked || 'all'
        },
        pagination: { current_page: 1, last_page: 1, pagesInRange: [] },

        init() {
            this.loadData();
            this.selectedIds = Array.isArray(this.selected) ? [...this.selected] : [this.selected].filter(Boolean);

            // 1. AO ABRIR O MODAL: Reconfigura o estado com base no novo contexto
            window.addEventListener('modal-open', (e) => {
                if (e.detail?.id !== 'selectorModal') return;

                this.currentContext = e.detail?.context || 'grid';
                this.modalOpenDetail = e.detail || {};

                // 🔄 RESET DE CONFIGURAÇÕES CONTEXTUAIS (Evita que o Tiptap herde do thumbnail)
                this.allowSizeSelect = e.detail?.allowSizeSelect !== undefined ? e.detail.allowSizeSelect : true;
                this.fixedSize = e.detail?.fixedSize || null;

                // 🔄 RESET DE SELEÇÃO: Se quem chamou passou itens, seleciona. Se não, ZERA!
                if (e.detail?.selected !== undefined) {
                    const sel = e.detail.selected;
                    this.selectedIds = Array.isArray(sel) ? [...sel] : [sel].filter(Boolean);
                } else {
                    this.selectedIds = [];
                }

                // Fecha a gaveta lateral ao abrir
                this.activeItem = null;

                // Se abriu com um item já selecionado (ex: thumbnail atual), localiza e ativa na gaveta
                if (this.selectedIds.length === 1 && !this.multiple && this.media.length > 0) {
                    const preSelected = this.media.find(m => m.id === this.selectedIds[0]);
                    if (preSelected) {
                        this.handleCardClick(preSelected);
                    }
                }
            });

            // 2. 🛡️ O PULO DO GATO: AO FECHAR O MODAL, RESETA O ESTADO IMEDIATAMENTE!
            window.addEventListener('modal-close', (e) => {
                if (e.detail?.id === 'selectorModal') {
                    this.resetGridState();
                }
            });
        },

        // Função de Reset Geral
        resetGridState() {
            this.selectedIds = [];
            this.activeItem = null;
            this.allowSizeSelect = true; // Volta ao padrão aberto
            this.fixedSize = null;       // Sem travas
            this.currentContext = 'grid';
            this.modalOpenDetail = {};
        },

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

        // Clique no card
        handleCardClick(item) {
            if (!this.selectable) return;

            if (this.multiple) {
                this.toggleSelection(item.id);
            } else {
                // Modo único: seleciona e abre a gaveta de detalhes
                this.selectedIds = [item.id];
                this.activeItem = { ...item };

                // Define a variante padrão
                if (this.fixedSize && item.variants?.[this.fixedSize]) {
                    this.selectedVariant = this.fixedSize;
                } else if (item.variants?.['large']) {
                    this.selectedVariant = 'large';
                } else {
                    this.selectedVariant = 'original';
                }
            }
        },

        toggleSelection(id) {
            const index = this.selectedIds.indexOf(id);
            if (index === -1) {
                this.selectedIds.push(id);
            } else {
                this.selectedIds.splice(index, 1);
            }
        },

        isSelected(id) {
            return this.selectedIds.includes(id);
        },

        clearSelection() {
            this.selectedIds = [];
            this.activeItem = null;
        },

        // Inserção da gaveta (item único com variante escolhida)
        insertCurrent() {
            if (!this.activeItem) return;

            const variantKey = this.fixedSize || this.selectedVariant || 'original';
            const variantObj = this.activeItem.variants?.[variantKey] || {};

            // Monta o payload enriquecido
            const payload = {
                ...this.activeItem,
                url: variantObj.url || this.activeItem.url,
                selected_variant: variantKey,
                width: variantObj.width || null,
                height: variantObj.height || null,
            };

            this.dispatchInserted(payload);
            this.clearSelection();
        },

        // Inserção múltipla
        insertBulk() {
            const selected = this.media.filter(m => this.selectedIds.includes(m.id));
            if (!selected.length) return;

            this.dispatchInserted(selected);
            this.clearSelection();
        },

        dispatchInserted(data) {
            if (typeof this.onSelect === 'function') {
                this.onSelect(data);
            } else {
                window.dispatchEvent(new CustomEvent('media:inserted', {
                    detail: {
                        media: data,
                        source: this.currentContext,
                        ...this.modalOpenDetail
                    }
                }));
            }
        },

        editItem(item) {
            window.dispatchEvent(new CustomEvent('media:edit', { detail: { item } }));
        },

        async deleteItem(item) {
            if (!(await Dialog.confirm(`Excluir "${item.name}" permanentemente?`))) return;

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
                    if (this.activeItem?.id === item.id) this.activeItem = null;
                    window.dispatchEvent(new CustomEvent('media:updated'));
                }
            } catch (err) {
                console.error('Erro ao excluir:', err);
                Dialog.alert('Erro ao excluir mídia.');
            }
        }
    }
}
</script>
@endpush
