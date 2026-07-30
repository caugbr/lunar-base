<nav class="admin-nav">
    @php
        $menuGroups = config('admin.menu', []);
        $injectedSections = \App\Support\AdminMenu::getInjectedSections();
        $injectedItems = \App\Support\AdminMenu::getInjectedItems();
        $injectedSubItems = \App\Support\AdminMenu::getInjectedSubItems();

        // 1. Processa novas seções (grupos) injetadas
        foreach ($injectedSections as $section) {
            $title = $section['title'];
            $items = $section['items'] ?? [];
            $index = $section['index'];

            // Verifica se a seção já existe pelo título
            $existingIndex = collect($menuGroups)->search(function($g) use ($title) {
                return strtolower($g['title'] ?? '') === strtolower($title);
            });

            if ($existingIndex !== false) {
                // Se já existe, apenas une os novos itens à seção existente
                $menuGroups[$existingIndex]['items'] = array_merge($menuGroups[$existingIndex]['items'] ?? [], $items);
            } else {
                $newGroup = ['title' => $title, 'items' => $items];
                if ($index !== null && $index >= 0 && $index <= count($menuGroups)) {
                    array_splice($menuGroups, $index, 0, [$newGroup]);
                } else {
                    $menuGroups[] = $newGroup;
                }
            }
        }

        // Helper: insere itens por posição 'after'
        $injectIntoItems = function(array $baseItems, array $injections) {
            $items = $baseItems;

            foreach ($injections as $injection) {
                $afterLabel = $injection['after'];
                $newItem = $injection['item'];

                if ($afterLabel !== null) {
                    $index = collect($items)->search(function($item) use ($afterLabel) {
                        return strtolower($item['label'] ?? '') === strtolower($afterLabel);
                    });

                    if ($index !== false) {
                        array_splice($items, $index + 1, 0, [$newItem]);
                        continue;
                    }
                }

                $items[] = $newItem;
            }

            return $items;
        };

        // Helper: verifica se algum filho do submenu está ativo
        $hasActiveChild = function($items) {
            if (empty($items)) return false;
            foreach ($items as $subItem) {
                if (request()->routeIs($subItem['active'] ?? '')) {
                    return true;
                }
            }
            return false;
        };

        // 2. Injeta itens de primeiro nível no grupo/seção correto (pelo groupIndex)
        foreach ($injectedItems as $injection) {
            $gIndex = $injection['groupIndex'] ?? 0;

            if (isset($menuGroups[$gIndex])) {
                $menuGroups[$gIndex]['items'] = $injectIntoItems($menuGroups[$gIndex]['items'] ?? [], [$injection]);
            } else {
                // Fallback: se o índice do grupo não existir, coloca no último disponível
                $lastIdx = count($menuGroups) - 1;
                if ($lastIdx >= 0) {
                    $menuGroups[$lastIdx]['items'] = $injectIntoItems($menuGroups[$lastIdx]['items'] ?? [], [$injection]);
                }
            }
        }
    @endphp

    @foreach($menuGroups as $group)
        {{-- Título da seção --}}
        @if(isset($group['title']) && !empty($group['items']))
            <div class="admin-nav-section">
                <span class="section-title">{{ $group['title'] }}</span>
            </div>
        @endif

        {{-- Itens da seção --}}
        @foreach($group['items'] ?? [] as $item)
            @php
                // Mescla sub-itens injetados para este item pai
                $parentLabel = $item['label'];
                $subInjections = $injectedSubItems[$parentLabel] ?? [];
                $item['items'] = $injectIntoItems($item['items'] ?? [], $subInjections);

                $isActive = request()->routeIs($item['active']);
                $hasChildren = !empty($item['items']);
                $childrenActive = $hasChildren ? $hasActiveChild($item['items']) : false;
                $isOpen = $isActive || $childrenActive;
                $childCount = count($item['items'] ?? []);
            @endphp

            @if($hasChildren)
                {{-- Item com submenu --}}
                <div class="admin-nav-dropdown {{ $isOpen ? 'open' : '' }}" style="--submenu-items: {{ $childCount }}">
                    <a href="{{ route($item['route']) }}"
                       class="admin-nav-item admin-nav-parent {{ $isOpen ? 'active' : '' }}">
                        <x-dynamic-component :component="'lucide-' . $item['icon']" class="lucid-icon" />
                        <span>{{ $item['label'] }}</span>
                        <span class="dropdown-arrow">
                            <x-lucide-chevron-down class="lucid-icon" />
                        </span>
                    </a>
                    <div class="admin-nav-submenu">
                        @foreach($item['items'] as $subItem)
                            @php
                                $isSubActive = request()->routeIs($subItem['active']);
                            @endphp
                            <a href="{{ route($subItem['route']) }}"
                               class="admin-nav-subitem {{ $isSubActive ? 'active' : '' }}">
                                <x-dynamic-component :component="'lucide-' . $subItem['icon']" class="lucid-icon" />
                                <span>{{ $subItem['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                {{-- Item simples --}}
                <a href="{{ route($item['route']) }}"
                   class="admin-nav-item {{ $isActive ? 'active' : '' }}">
                    <x-dynamic-component :component="'lucide-' . $item['icon']" class="lucid-icon" />
                    <span>{{ $item['label'] }}</span>
                </a>
            @endif
        @endforeach
    @endforeach
</nav>
