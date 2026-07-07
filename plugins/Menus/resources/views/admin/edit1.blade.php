@extends('admin.layout')
@section('header_title', 'Estrutura do Menu')
@section('header_subtitle', 'Editando links de: ' . $menu->name)
@section('content')

{{-- Injeta o CSS externo do plugin de forma isolada seguindo sua diretriz --}}
@once
@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/menus/css/menus.css') }}">
@endpush
@endonce

<div class="menu-builder-container" x-data="menuBuilder({{ $itemsJson }})" x-cloak>

    {{-- Coluna da Esquerda (Fontes de Links) --}}
    <div class="sources-column">
        <div class="edit-box">
            <header>Adicionar Itens ao Menu</header>
            <article>

                <!-- Sanfona 1: Páginas -->
                <div class="accordion-item" x-data="{ open: false }">
                    <button type="button" class="accordion-header" @click="open = !open">
                        <span>Páginas Dinâmicas</span>
                        <x-lucide-chevron-down class="lucid-icon" x-show="!open" />
                        <x-lucide-chevron-up class="lucid-icon" x-show="open" />
                    </button>
                    <div class="accordion-body" x-show="open">
                        <div class="checkbox-list">
                            @foreach($pages as $page)
                            <label class="checkbox-label">
                                <input type="checkbox" value="{{ $page->id }}" data-title="{{ $page->title }}" data-type="page" data-model="App\Models\Page">
                                <span>{{ $page->title }}</span>
                            </label>
                            @endforeach
                        </div>
                        <button type="button" @click="addSelectedToMenu($el)" class="admin-btn admin-btn-secondary btn-sm" style="width: 100%; margin-top: 1rem;">
                            Adicionar ao Menu
                        </button>
                    </div>
                </div>

                <!-- Sanfona 2: Posts -->
                <div class="accordion-item" x-data="{ open: false }">
                    <button type="button" class="accordion-header" @click="open = !open">
                        <span>Posts do Blog</span>
                        <x-lucide-chevron-down class="lucid-icon" x-show="!open" />
                        <x-lucide-chevron-up class="lucid-icon" x-show="open" />
                    </button>
                    <div class="accordion-body" x-show="open">
                        <div class="checkbox-list">
                            @foreach($posts as $post)
                            <label class="checkbox-label">
                                <input type="checkbox" value="{{ $post->id }}" data-title="{{ $post->title }}" data-type="post" data-model="App\Models\Post">
                                <span>{{ $post->title }}</span>
                            </label>
                            @endforeach
                        </div>
                        <button type="button" @click="addSelectedToMenu($el)" class="admin-btn admin-btn-secondary btn-sm" style="width: 100%; margin-top: 1rem;">
                            Adicionar ao Menu
                        </button>
                    </div>
                </div>

                <!-- Sanfona 3: Links Customizados -->
                <div class="accordion-item" x-data="{ open: false, url: '', label: '' }">
                    <button type="button" class="accordion-header" @click="open = !open">
                        <span>Links Personalizados</span>
                        <x-lucide-chevron-down class="lucid-icon" x-show="!open" />
                        <x-lucide-chevron-up class="lucid-icon" x-show="open" />
                    </button>
                    <div class="accordion-body" x-show="open" style="padding: 12px;">
                        <div class="form-group" style="margin-bottom: 10px;">
                            <label style="font-size: 0.8rem;">URL</label>
                            <input type="text" x-model="url" placeholder="https://exemplo.com" class="form-input">
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="font-size: 0.8rem;">Rótulo do Link</label>
                            <input type="text" x-model="label" placeholder="Texto visível" class="form-input">
                        </div>
                        <button type="button"
                                @click="addCustomLink(url, label); url = ''; label = ''"
                                :disabled="!url.trim() || !label.trim()"
                                class="admin-btn admin-btn-secondary btn-sm" style="width: 100%;">
                            Adicionar ao Menu
                        </button>
                    </div>
                </div>

            </article>
        </div>
    </div>

    {{-- Coluna da Direita (Estrutura em Árvore) --}}
    <div class="builder-column">
        <div class="admin-card" style="margin-bottom: 0;">
            <div class="admin-card-header">
                <h2><x-lucide-menu class="lucid-icon" /> Estrutura do Menu</h2>
                <a href="{{ route('admin.menus.index') }}" class="admin-btn admin-btn-secondary">
                    <x-lucide-arrow-left class="lucid-icon" /> Voltar
                </a>
            </div>

            <p style="font-size: 0.875rem; color: var(--color-text-muted); margin-bottom: 1.5rem;">
                Adicione links a partir da coluna esquerda, use os controles de setas para organizar a sequência e aninhar sub-menus, e clique em "Salvar Estrutura".
            </p>

            {{-- Área de Renderização da Árvore Visual --}}
            <div class="menu-items-tree">
                <template x-if="flatItems.length === 0">
                    <p style="text-align: center; color: var(--color-text-dim); padding: 3rem 0;">
                        Este menu está vazio. Adicione links para começar.
                    </p>
                </template>

                <div class="tree-list">
                    <template x-for="(item, index) in flatItems" :key="index">
                        <div class="tree-item-wrapper" :style="`padding-left: ${item.depth * 2.5}rem;`" :class="{ 'has-depth': item.depth > 0 }">
                            <div class="tree-item">
                                {{-- Tipo / Rótulo --}}
                                <div class="tree-item-title">
                                    <span class="item-type-badge" x-text="item.type.toUpperCase()"></span>
                                    <strong x-text="item.label"></strong>
                                    <template x-if="item.type === 'custom'">
                                        <small x-text="item.url" class="admin-text-muted" style="margin-left: 8px; font-weight: normal;"></small>
                                    </template>
                                </div>

                                {{-- Controles de Movimentação --}}
                                <div class="tree-item-controls">
                                    <button type="button" @click="moveUp(index)" :disabled="index === 0" title="Subir">
                                        <x-lucide-chevron-up class="lucid-icon" />
                                    </button>
                                    <button type="button" @click="moveDown(index)" :disabled="index === flatItems.length - 1" title="Descer">
                                        <x-lucide-chevron-down class="lucid-icon" />
                                    </button>
                                    <button type="button" @click="indent(index)" :disabled="index === 0 || flatItems[index].depth > flatItems[index-1].depth" title="Aninhar (Sub-menu)">
                                        <x-lucide-chevron-right class="lucid-icon" />
                                    </button>
                                    <button type="button" @click="outdent(index)" :disabled="item.depth === 0" title="Recuar nível">
                                        <x-lucide-chevron-left class="lucid-icon" />
                                    </button>
                                    <button type="button" @click="toggleEdit(index)" class="control-btn-edit" title="Configurações extras">
                                        <x-lucide-settings class="lucid-icon" />
                                    </button>
                                    <button type="button" @click="removeItem(index)" class="control-btn-delete" title="Remover">
                                        <x-lucide-x class="lucid-icon" />
                                    </button>
                                </div>
                            </div>

                            {{-- Painel de Configurações Extras do Item --}}
                            <div class="tree-item-settings" x-show="item.editing" x-transition>
                                <div class="admin-form-row">
                                    <div class="form-group">
                                        <label style="font-size: 0.75rem;">Rótulo de exibição</label>
                                        <input type="text" x-model="item.label" class="form-input">
                                    </div>
                                    <div class="form-group">
                                        <label style="font-size: 0.75rem;">Classe CSS extra (opcional)</label>
                                        <input type="text" x-model="item.class" placeholder="ex: btn-destaque" class="form-input">
                                    </div>
                                    <div class="form-group">
                                        <label style="font-size: 0.75rem;">Destino do Link</label>
                                        <select x-model="item.target" class="form-input">
                                            <option value="_self">Abrir na mesma guia</option>
                                            <option value="_blank">Abrir em nova guia (_blank)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="buttons" style="margin-top: 2rem;">
                <button type="button" @click="saveMenuStructure()" :disabled="saving" class="admin-btn admin-btn-primary">
                    <template x-if="saving">
                        <x-lucide-loader class="lucid-icon animate-spin" />
                    </template>
                    <span x-text="saving ? 'Salvando...' : 'Salvar Estrutura'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    function menuBuilder(initialItems) {
        return {
            flatItems: [],
            saving: false,

            init() {
                // Ao iniciar, transforma a árvore aninhada do banco em uma lista plana controlada por profundidade (depth)
                this.flatItems = this.flattenTree(initialItems);
            },

            // Converte árvore aninhada para lista plana controlada por "depth" (Profundidade)
            flattenTree(tree, depth = 0) {
                let flat = [];
                tree.forEach(item => {
                    flat.push({
                        label: item.label,
                        type: item.type,
                        url: item.url || '',
                        model_type: item.model_type || null,
                        model_id: item.model_id || null,
                        target: item.target || '_self',
                        class: item.class || '',
                        depth: depth,
                        editing: false
                    });
                    if (item.children && item.children.length > 0) {
                        flat = flat.concat(this.flattenTree(item.children, depth + 1));
                    }
                });
                return flat;
            },

            // Transforma a lista plana de volta em árvore aninhada antes de enviar para o banco
            unflattenTree() {
                let tree = [];
                let stack = [{ depth: -1, children: tree }];

                this.flatItems.forEach(item => {
                    let node = {
                        label: item.label,
                        type: item.type,
                        url: item.url,
                        model_type: item.model_type,
                        model_id: item.model_id,
                        target: item.target,
                        class: item.class,
                        children: []
                    };

                    while (stack.length > 0 && stack[stack.length - 1].depth >= item.depth) {
                        stack.pop();
                    }

                    if (stack.length > 0) {
                        stack[stack.length - 1].children.push(node);
                    }
                    stack.push({ depth: item.depth, children: node.children });
                });

                return tree;
            },

            addCustomLink(url, label) {
                this.flatItems.push({
                    label: label,
                    type: 'custom',
                    url: url,
                    model_type: null,
                    model_id: null,
                    target: '_self',
                    class: '',
                    depth: 0,
                    editing: false
                });
            },

            addSelectedToMenu(btnEl) {
                const container = btnEl.closest('.accordion-body');
                const checkboxes = container.querySelectorAll('input[type="checkbox"]:checked');

                checkboxes.forEach(cb => {
                    this.flatItems.push({
                        label: cb.dataset.title,
                        type: cb.dataset.type,
                        url: '',
                        model_type: cb.dataset.model,
                        model_id: parseInt(cb.value),
                        target: '_self',
                        class: '',
                        depth: 0,
                        editing: false
                    });
                    cb.checked = false; // Desmarca após injetar
                });
            },

            removeItem(index) {
                this.flatItems.splice(index, 1);
            },

            toggleEdit(index) {
                this.flatItems[index].editing = !this.flatItems[index].editing;
            },

            // Controles de Ordenação por Setas
            moveUp(index) {
                if (index > 0) {
                    let temp = this.flatItems[index];
                    this.flatItems[index] = this.flatItems[index - 1];
                    this.flatItems[index - 1] = temp;
                }
            },

            moveDown(index) {
                if (index < this.flatItems.length - 1) {
                    let temp = this.flatItems[index];
                    this.flatItems[index] = this.flatItems[index + 1];
                    this.flatItems[index + 1] = temp;
                }
            },

            // Aninhar (Sub-menu para a Direita)
            indent(index) {
                if (index > 0) {
                    const prevItem = this.flatItems[index - 1];
                    if (this.flatItems[index].depth <= prevItem.depth) {
                        this.flatItems[index].depth++;
                    }
                }
            },

            // Recuar nível para a Esquerda
            outdent(index) {
                if (this.flatItems[index].depth > 0) {
                    this.flatItems[index].depth--;
                }
            },

            // Envia toda a árvore serializada via POST para o controlador
            async saveMenuStructure() {
                this.saving = true;
                const tree = this.unflattenTree();

                try {
                    const response = await fetch("{{ route('admin.menus.save_items', $menu->id) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            items_json: JSON.stringify(tree)
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        alert(data.message);
                    } else {
                        alert(data.message || 'Erro ao salvar o menu.');
                    }

                } catch (e) {
                    console.error('Erro ao salvar menu:', e);
                    alert('Erro de conexão com o servidor.');
                } finally {
                    this.saving = false;
                }
            }
        }
    }
</script>
@endpush
@endsection
