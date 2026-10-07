@props([
    'model' => null,
    'type'  => null,
    'title' => '',
    'index_title' => 'Indexação nos buscadores',
    'index_desc' => 'Buscadores podem indexar esta publicação?',
    'follow_title' => 'Seguir links',
    'follow_desc' => 'Buscadores podem seguir os links desta publicação?',
])

@php
    $typeKey = $type ?? ($model ? strtolower(class_basename($model)) : 'post');
    $id      = $model?->id;

    // Busca a option salva para este registro específico
    $saved = $id ? getOption("robots_{$typeKey}_{$id}", null) : null;

    // Fallbacks para as configurações globais do sistema
    $globalIndex  = (bool) setting('general.robots_index', true);
    $globalFollow = (bool) setting('general.robots_follow', true);

    // Se já foi salvo individualmente usa o salvo, senão assume o padrão global
    $indexChecked  = $saved !== null ? (bool) ($saved['index'] ?? true) : $globalIndex;
    $followChecked = $saved !== null ? (bool) ($saved['follow'] ?? true) : $globalFollow;
@endphp

@if(!empty($title))
<h3>{{ $title }}</h3>
@endif
<div class="form-group robots-field-item robots-index">
    <label>{{ $index_title }}</label>
    <x-switch
        name="robots_index"
        id="robots_index"
        :checked="$indexChecked"
        active="Indexar"
        inactive="Não indexar"
    />
    <small>{{ $index_desc }}</small>
</div>

<div class="form-group robots-field-item robots-follow">
    <label>{{ $follow_title }}</label>
    <x-switch
        name="robots_follow"
        id="robots_follow"
        :checked="$followChecked"
        active="Seguir"
        inactive="Não seguir"
    />
    <small>{{ $follow_desc }}</small>
</div>
