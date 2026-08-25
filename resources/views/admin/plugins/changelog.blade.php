@php
    // Garante que o changelog seja tratado como array para evitar erros de tipo
    $changelog = (array) $changelog;

    $version = $changelog['version'] ?? 'Sem versão';
    $rawDate = $changelog['date'] ?? null;
    $date    = $rawDate ? date('d/m/Y', strtotime($rawDate)) : null;

    $added   = $changelog['added'] ?? [];
    $changed = $changelog['changed'] ?? [];
    $fixed   = $changelog['fixed'] ?? [];

    $hasContent = !empty($added) || !empty($changed) || !empty($fixed);
@endphp

<div class="plugin-changelog-wrap" style="font-family: system-ui, -apple-system, sans-serif; color: #374151; text-align: left;">

    <!-- Cabeçalho da Versão -->
    <div style="border-bottom: 1px solid #e5e7eb; padding-bottom: 1rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
        <div style="font-size: 1.25rem; font-weight: 700; color: #111827;">
            {{ $name }} v{{ $version }}
        </div>
        @if($date)
            <div style="font-size: 0.85rem; color: #4b5563; background-color: #f3f4f6; padding: 4px 12px; border-radius: 20px; font-weight: 500;">
                Lançada em {{ $date }}
            </div>
        @endif
    </div>

    @if($hasContent)
        <!-- Novidades (Added) -->
        @if(!empty($added))
            <div style="margin-bottom: 1.5rem;">
                <span style="display: inline-block; background-color: #ecfdf5; color: #065f46; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; padding: 2px 8px; border-radius: 4px; margin-bottom: 0.5rem; letter-spacing: 0.5px;">
                    Novidades
                </span>
                <ul style="margin: 0; padding-left: 1.25rem; list-style-type: disc;">
                    @foreach($added as $item)
                        <li style="font-size: 0.9rem; line-height: 1.5; color: #4b5563; margin-bottom: 0.35rem;">
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Alterações (Changed) -->
        @if(!empty($changed))
            <div style="margin-bottom: 1.5rem;">
                <span style="display: inline-block; background-color: #eff6ff; color: #1e40af; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; padding: 2px 8px; border-radius: 4px; margin-bottom: 0.5rem; letter-spacing: 0.5px;">
                    Alterações
                </span>
                <ul style="margin: 0; padding-left: 1.25rem; list-style-type: disc;">
                    @foreach($changed as $item)
                        <li style="font-size: 0.9rem; line-height: 1.5; color: #4b5563; margin-bottom: 0.35rem;">
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Correções (Fixed) -->
        @if(!empty($fixed))
            <div style="margin-bottom: 1.5rem;">
                <span style="display: inline-block; background-color: #fef2f2; color: #991b1b; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; padding: 2px 8px; border-radius: 4px; margin-bottom: 0.5rem; letter-spacing: 0.5px;">
                    Correções
                </span>
                <ul style="margin: 0; padding-left: 1.25rem; list-style-type: disc;">
                    @foreach($fixed as $item)
                        <li style="font-size: 0.9rem; line-height: 1.5; color: #4b5563; margin-bottom: 0.35rem;">
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    @else
        <!-- Fallback caso não haja conteúdo parseado -->
        <p style="font-size: 0.9rem; color: #6b7280; text-align: center; font-style: italic; margin: 2rem 0;">
            Nenhum detalhe de alteração informado para esta versão.
        </p>
    @endif

</div>
