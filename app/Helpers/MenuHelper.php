<?php

if (! function_exists('setMenuBadge')) {
    /**
     * Define um badge num item de primeiro nível do menu lateral pelo Label.
     *
     * @param string          $label O label do item (ex: 'Plugins', 'Configurações', 'Avisos')
     * @param int|string|null $badge Valor do badge (número, símbolo, texto curto)
     * @return void
     */
    function setMenuBadge(string $label, int|string|null $badge, string $title = ''): void
    {
        if ($badge === 0 || $badge === '' || $badge === null || $badge === '0') {
            return;
        }

        addFilter('adminMenuGroups', function (array $menuGroups) use ($label, $badge): array {
            $target = mb_strtolower($label);

            return array_map(function (array $group) use ($target, $badge) {
                if (empty($group['items'])) {
                    return $group;
                }

                $group['items'] = array_map(function (array $item) use ($target, $badge) {
                    if (mb_strtolower($item['label'] ?? '') === $target) {
                        $item['badgeCount'] = $badge;
                        if (!empty($title)) {
                            $item['badgeTitle'] = $title;
                        }
                    }
                    return $item;
                }, $group['items']);

                return $group;
            }, $menuGroups);
        });
    }
}
