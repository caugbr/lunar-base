@props([
    'model' => null
])

@php
    // Usa o model passado na prop OU pega o Model resolvido da rota atual
    $current = $model ?? collect(request()->route()?->parameters())->first(function ($param) {
        return $param instanceof \Illuminate\Database\Eloquent\Model;
    });

    // Settings globais como padrão
    $index  = (bool) setting('general.robots_index', true);
    $follow = (bool) setting('general.robots_follow', true);

    // Se achou o Model com ID, puxa a option específica dele
    if ($current && isset($current->id)) {
        $typeKey = strtolower(class_basename($current));
        $saved   = getOption("robots_{$typeKey}_{$current->id}", null);

        if ($saved !== null) {
            $index  = (bool) ($saved['index'] ?? true);
            $follow = (bool) ($saved['follow'] ?? true);
        }
    }

    $directive = ($index ? 'index' : 'noindex') . ', ' . ($follow ? 'follow' : 'nofollow');
@endphp

@if($directive !== 'index, follow')
<meta name="robots" content="{{ $directive }}">
@endif
