<nav class="admin-nav">
                @php
                    $menuGroups = config('admin.menu', []);
                    $injectedItems = \App\Support\AdminMenu::getInjectedItems();
                    $injectedSubItems = \App\Support\AdminMenu::getInjectedSubItems();

                    // Helper: insere itens injetados em um array de items (nível 1 ou submenu)
                    $injectIntoItems = function(array $baseItems, array $injections) {
                        $items = $baseItems;

                        foreach ($injections as $injection) {
                            $afterLabel = $injection['after'];
                            $newItem = $injection['item'];

                            $index = collect($items)->search(function($item) use ($afterLabel) {
                                return strtolower($item['label']) === strtolower($afterLabel);
                            });

                            if ($index !== false) {
                                array_splice($items, $index + 1, 0, [$newItem]);
                            } else {
                                $items[] = $newItem;
                            }
                        }

                        return $items;
                    };

                    // Helper: verifica se algum item do submenu está ativo
                    $hasActiveChild = function($items) {
                        if (empty($items)) return false;
                        foreach ($items as $subItem) {
                            if (request()->routeIs($subItem['active'])) {
                                return true;
                            }
                        }
                        return false;
                    };

                    // Injeta itens de primeiro nível
                    foreach ($injectedItems as $injection) {
                        foreach ($menuGroups as &$group) {
                            $group['items'] = $injectIntoItems($group['items'] ?? [], [$injection]);
                        }
                    }
                @endphp


                @foreach($menuGroups as $group)
                    {{-- Título da seção --}}
                    @if(isset($group['title']))
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
                            {{-- Item com submenu: link funcional + dropdown no hover --}}
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
                            {{-- Item simples (sem submenu) --}}
                            <a href="{{ route($item['route']) }}"
                               class="admin-nav-item {{ $isActive ? 'active' : '' }}">
                                <x-dynamic-component :component="'lucide-' . $item['icon']" class="lucid-icon" />
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endif
                    @endforeach
                @endforeach
            </nav>
