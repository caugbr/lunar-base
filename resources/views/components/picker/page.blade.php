@props([
    'name',
    'label' => null,
    'selected' => null,
    'value' => null,
    'exclude' => null,
    'orderBy' => 'title',
    'valueField' => 'id',
    'labelField' => 'title',
    'status' => 'published',
    'placeholder' => '-- Selecionar página --',
    'showNamespace' => false,
    'required' => false,
    'help' => null,
])

@php
    $columns = [$valueField, $labelField, $orderBy];
    if ($showNamespace) {
        $columns[] = 'namespace';
    }
    $columns = array_unique($columns);

    $query = \App\Models\Page::select($columns);

    if ($exclude !== null) {
        $query->where($valueField, '!=', $exclude);
    }

    if ($status !== 'all' && $status !== 'any' && $status !== null) {
        if (is_array($status)) {
            $query->whereIn('status', $status);
        } else {
            $query->where('status', $status);
        }
    }

    $pages = $query->orderBy($orderBy)->get();

    $options = [];
    foreach ($pages as $page) {
        $optVal = $page->{$valueField};
        $optLabel = $page->{$labelField};

        if ($showNamespace && !empty($page->namespace)) {
            $optLabel = $page->namespace . ' / ' . $optLabel;
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
