@if(!empty($config))
<div class="plugin-config-help" style="margin-top: 1.5rem;">
    <h4 style="margin-bottom: 0.5rem;">
        Configurações do Desenvolvedor (<code>config/pluginSettings.php</code>)
    </h4>
    <p style="font-size: 0.875rem; color: var(--color-text-muted, #64748b); margin-bottom: 0.75rem;">
        Você pode personalizar este plugin adicionando o bloco abaixo ao arquivo <code>config/pluginSettings.php</code> na raiz do site:
    </p>
    <div class="code" style="font-family: monospace; padding: 14px 18px; background: #1e293b; color: #38bdf8; border-radius: 6px; font-size: 0.85rem; line-height: 1.7; overflow-x: auto;">
        '{{ $plugin }}' => [<br>
        @foreach($config as $key => $item)
            @php
                $default = $item['default'] ?? null;
                $val = is_string($default) ? "'{$default}'" : var_export($default, true);
                $desc = !empty($item['description']) ? " // " . $item['description'] : '';
            @endphp
            &nbsp;&nbsp;&nbsp;&nbsp;'<span style="color: #f1f5f9;">{{ $key }}</span>' => <span style="color: #f59e0b;">{{ $val }}</span>,{{ $desc }}<br>
        @endforeach
        ],
    </div>
</div>
@endif
