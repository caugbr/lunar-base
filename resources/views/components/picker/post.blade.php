@props([
    'name',
    'label' => null,
    'selected' => null,
    'value' => null,
    'placeholder' => '-- Selecionar publicação --',
    'exclude' => null,
    'status' => 'all',
    'valueField' => 'id',
    'labelField' => 'title',
    'showBadges' => true,
    'required' => false,
    'help' => null,
])

@php
    $query = \App\Models\Post::query();

    if ($status !== 'all' && $status !== null) {
        if (is_array($status)) {
            $query->whereIn('status', $status);
        } else {
            $query->where('status', $status);
        }
    }

    if (!empty($exclude)) {
        $query->where($valueField, '!=', $exclude);
    }

    $columns = array_unique([$valueField, $labelField, 'status', 'sticky', 'featured', 'published_at', 'created_at']);

    $posts = $query->orderBy('sticky', 'desc')
        ->orderBy('published_at', 'desc')
        ->orderBy('created_at', 'desc')
        ->get($columns);

    $options = [];
    foreach ($posts as $post) {
        $optVal = $post->{$valueField};
        $optLabel = $post->{$labelField};

        if ($showBadges) {
            $badges = [];

            if (!empty($post->sticky)) {
                $badges[] = 'FIXADO';
            }

            if (!empty($post->featured)) {
                $badges[] = '★';
            }

            if ($post->status !== 'published') {
                $badges[] = ucfirst($post->status);
            }

            if (!empty($badges)) {
                $optLabel = '[' . implode(' | ', $badges) . '] ' . $optLabel;
            }
        }

        $options[$optVal] = $optLabel;
    }

    $currentValue = $value ?? $selected;
@endphp

<x-form.select
    :name="$name"
    :label="$label"
    :options="$options"
    :value="$currentValue"
    :placeholder="$placeholder"
    :required="$required"
    :help="$help"
    {{ $attributes }}
/>
