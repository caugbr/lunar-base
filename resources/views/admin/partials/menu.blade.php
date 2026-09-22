<nav class="admin-nav">
    @php
        $user = auth()->user();

        // Função universal de checagem de permissão e role
        $canAccess = function(array $node) use ($user): bool {
            if (!$user) return false;

            // 1. Checagem por role (se houver)
            if (isset($node['role'])) {
                $allowedRoles = is_array($node['role'])
                    ? $node['role']
                    : array_map('trim', explode(',', $node['role']));

                if (!in_array($user->role, $allowedRoles, true)) {
                    return false;
                }
            }

            if (isset($node['permission'])) {
                $perms = is_array($node['permission'])
                    ? $node['permission']
                    : array_map('trim', explode(',', $node['permission']));

                $positivePerms = [];
                $negativePerms = [];

                foreach ($perms as $p) {
                    if (str_starts_with($p, '!')) {
                        $negativePerms[] = substr($p, 1);
                    } else {
                        $positivePerms[] = $p;
                    }
                }

                // REGRA NEGATIVA: Se o usuário tiver qualquer uma das permissões com '!', bloqueia imediatamente
                foreach ($negativePerms as $cleanPerm) {
                    if ($user->hasPermission($cleanPerm)) {
                        return false;
                    }
                }

                // REGRA POSITIVA (OR): Se foram informadas permissões positivas, ele precisa ter ao menos UMA
                if (!empty($positivePerms)) {
                    if (!$user->hasPermission($positivePerms)) {
                        return false;
                    }
                }
            }

            return true;
        };

        $menuGroups = config('admin.menu', []);
        $injectedSections = \App\Support\AdminMenu::getInjectedSections();
        $injectedItems = \App\Support\AdminMenu::getInjectedItems();
        $injectedSubItems = \App\Support\AdminMenu::getInjectedSubItems();

        // Processa novas seções (grupos) injetadas
        foreach ($injectedSections as $section) {
            $title = $section['title'];
            $items = $section['items'] ?? [];
            $index = $section['index'];

            $existingIndex = collect($menuGroups)->search(function($g) use ($title) {
                return strtolower($g['title'] ?? '') === strtolower($title);
            });

            if ($existingIndex !== false) {
                $menuGroups[$existingIndex]['items'] = array_merge($menuGroups[$existingIndex]['items'] ?? [], $items);
            } else {
                $newGroup = [
                    'title'      => $title,
                    'items'      => $items,
                    'role'       => $section['role'] ?? null,
                    'permission' => $section['permission'] ?? null,
                ];

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

        $hasActiveChild = function($items) {
            if (empty($items)) return false;
            foreach ($items as $subItem) {
                if (request()->routeIs($subItem['active'] ?? '')) {
                    return true;
                }
            }
            return false;
        };

        // Injeta itens de primeiro nível nas seções
        foreach ($injectedItems as $injection) {
            $gIndex = $injection['groupIndex'] ?? 0;

            if (isset($menuGroups[$gIndex])) {
                $menuGroups[$gIndex]['items'] = $injectIntoItems($menuGroups[$gIndex]['items'] ?? [], [$injection]);
            } else {
                $lastIdx = count($menuGroups) - 1;
                if ($lastIdx >= 0) {
                    $menuGroups[$lastIdx]['items'] = $injectIntoItems($menuGroups[$lastIdx]['items'] ?? [], [$injection]);
                }
            }
        }

        // Permite que plugins filtrem ou alterem a árvore
        $menuGroups = apply_filters('adminMenuGroups', $menuGroups);
    @endphp

    @foreach($menuGroups as $group)
        @php
            // 1. O GRUPO INTEIRO TEM ACESSO? (se tiver role ou permission no grupo)
            if (!$canAccess($group)) {
                continue;
            }

            // 2. Filtra os itens principais deste grupo
            $groupItems = array_filter($group['items'] ?? [], function($item) use ($canAccess) {
                // Suporte a settings de exibição globais
                if (($item['label'] ?? '') === 'Referências' && !setting('navigation.show_references')) {
                    return false;
                }
                if (($item['label'] ?? '') === 'Temas' && setting('navigation.hide_themes')) {
                    return false;
                }

                return $canAccess($item);
            });

            // Se o grupo não tiver nenhum item visível, pula a renderização do grupo inteiro!
            if (empty($groupItems)) {
                continue;
            }
        @endphp

        {{-- Título da seção --}}
        @if(!empty($group['title']))
            <div class="admin-nav-section">
                <span class="section-title">{{ $group['title'] }}</span>
            </div>
        @endif

        {{-- Itens da seção --}}
        @foreach($groupItems as $item)
            @php
                $parentLabel = $item['label'];
                $subInjections = $injectedSubItems[$parentLabel] ?? [];
                $allSubItems = $injectIntoItems($item['items'] ?? [], $subInjections);

                // 3. Filtra os sub-itens pela permissão/role de cada um
                $visibleSubItems = array_filter($allSubItems, function($sub) use ($canAccess) {
                    return $canAccess($sub);
                });

                $isActive = request()->routeIs($item['active']);
                $hasChildren = !empty($visibleSubItems);
                $childrenActive = $hasChildren ? $hasActiveChild($visibleSubItems) : false;
                $isOpen = $isActive || $childrenActive;
                $childCount = count($visibleSubItems);
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
                        @foreach($visibleSubItems as $subItem)
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
