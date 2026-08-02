

@if(isset($taxonomies) && count($taxonomies))
<div class="edit-box">
    <header>Taxonomias</header>
    <article>
        <div class="form-group">
            @foreach($taxonomies as $taxonomy)
                <div class="taxonomy-group">
                    <h4>
                        {{ $taxonomy->name }}
                        @if($taxonomy->description)
                            <small>({{ $taxonomy->description }})</small>
                        @endif
                    </h4>
                    <div class="terms-checkbox-group">
                        @if($taxonomy->unique)
                            <select name="term_ids[]" id="tax_{{ $taxonomy->id }}">
                                <option value="">-- Sem {{ $taxonomy->name }} --</option>
                                @foreach($taxonomy->terms as $term)
                                    <option value="{{ $term->id }}"
                                        {{ isset($selectedTermIds) && in_array($term->id, $selectedTermIds) ? 'selected' : '' }}>
                                        {{ $term->name }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                        @foreach($taxonomy->terms as $term)
                            <label>
                                <input type="checkbox" name="term_ids[]" value="{{ $term->id }}"
                                    {{ isset($selectedTermIds) && in_array($term->id, $selectedTermIds) ? 'checked' : '' }}>
                                <span>{{ $term->name }}</span>
                                @if($term->parent)
                                    <small>({{ $term->parent->name }})</small>
                                @endif
                            </label>
                        @endforeach
                        @endif
                    </div>
                </div>
            @endforeach
            @php
                $text = $type == 'post' ? 'este post' : 'esta página';
            @endphp
            <small>Selecione os termos que classificam {{ $text }}</small>
        </div>
    </article>
</div>
@endif
