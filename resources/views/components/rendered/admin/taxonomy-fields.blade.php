@if(isset($taxonomies) && count($taxonomies))
<div class="edit-box">
    <header>Taxonomias</header>
    <article>
        <div class="form-group">
            @foreach($taxonomies as $taxonomy)
                @continue($taxonomy->terms->isEmpty())

                @php
                    $termOptions = $taxonomy->terms->mapWithKeys(function ($term) {
                        $label = $term->parent ? "{$term->name} ({$term->parent->name})" : $term->name;
                        return [$term->id => $label];
                    })->all();

                    $selectedTaxTerm = $taxonomy->terms
                        ->pluck('id')
                        ->first(fn($id) => in_array($id, $selectedTermIds ?? []));
                @endphp

                <div class="taxonomy-group">
                    <h4>
                        {{ $taxonomy->name }}
                        @if($taxonomy->description)
                            <small>({{ $taxonomy->description }})</small>
                        @endif
                    </h4>

                    <div class="terms-checkbox-group">
                        @if($taxonomy->unique)
                            <x-form.select
                                name="term_ids[]"
                                id="tax_{{ $taxonomy->id }}"
                                :options="$termOptions"
                                :value="$selectedTaxTerm"
                                :placeholder="'-- Sem ' . $taxonomy->name . ' --'"
                            />
                        @else
                            <x-form.checkbox
                                name="term_ids[]"
                                id="tax_{{ $taxonomy->id }}"
                                :options="$termOptions"
                                :value="$selectedTermIds ?? []"
                                inline="1"
                            />
                        @endif
                    </div>
                </div>
            @endforeach

            @php
                $targetLabel = ($type ?? 'page') === 'post' ? 'este post' : 'esta página';
            @endphp
            <small class="form-help">Selecione os termos que classificam {{ $targetLabel }}</small>
        </div>
    </article>
</div>
@endif

@once
<style>
.taxonomy-group {
    margin-bottom: 1rem;
    padding: 0.75rem;
    background: #f9fafb;
    border-radius: 0.5rem;
}
.taxonomy-group h4 {
    margin: 0 0 0.75rem 0;
    font-size: 1.1rem;
    font-weight: 600;
}
.taxonomy-group h4 small {
    font-weight: normal;
    color: #6b7280;
}
.terms-checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}
.terms-checkbox-group > .form-field {
    margin-bottom: 0;
}
.terms-checkbox-group label {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}
.terms-checkbox-group small {
    color: #9ca3af;
}
</style>
@endonce
