@props([
    'name' => 'meta',
    'existingKeys' => [],
    'values' => [],
])

@php
    $resolvedValues = old($name, $values);

    $pairs = [];
    if (is_array($resolvedValues) || $resolvedValues instanceof \Illuminate\Support\Collection) {
        foreach ($resolvedValues as $key => $val) {
            if (is_array($val) && array_key_exists('key', $val)) {
                $pairs[] = [
                    'key' => (string) ($val['key'] ?? ''),
                    'value' => (string) ($val['value'] ?? ''),
                ];
            } else {
                $pairs[] = [
                    'key' => (string) $key,
                    'value' => (string) $val,
                ];
            }
        }
    }

    if (empty($pairs)) {
        $pairs[] = ['key' => '', 'value' => ''];
    }
@endphp

<div class="meta-editor" id="{{ $name }}_editor" data-meta-name="{{ $name }}">
    <template id="{{ $name }}_template">
        <div class="meta-pair">
            <div class="meta-key-group">
                <x-form.select
                    name="__NAME__[__INDEX__][key]"
                    :options="$existingKeys"
                    placeholder="-- Selecione ou insira --"
                    allow-insert
                    insert-label="Inserir nova chave..."
                    insert-placeholder="Nova chave (Enter para confirmar)"
                />
            </div>

            <div class="meta-value-group">
                <input
                    type="text"
                    name="__NAME__[__INDEX__][value]"
                    placeholder="Valor"
                    class="form-control meta-value-input"
                >
            </div>

            <button type="button" class="meta-remove" title="Remover metadado">
                <x-lucide-trash-2 class="lucid-icon" />
            </button>
        </div>
    </template>

    <div class="meta-pairs-list">
        @foreach($pairs as $index => $pair)
            <div class="meta-pair">
                <div class="meta-key-group">
                    <x-form.select
                        :name="$name . '[' . $index . '][key]'"
                        :options="$existingKeys"
                        :value="$pair['key']"
                        placeholder="-- Selecione ou insira --"
                        allow-insert
                        insert-label="Inserir nova chave..."
                        insert-placeholder="Nova chave (Enter para confirmar)"
                    />
                </div>

                <div class="meta-value-group">
                    <input
                        type="text"
                        name="{{ $name }}[{{ $index }}][value]"
                        value="{{ $pair['value'] }}"
                        placeholder="Valor"
                        class="form-control meta-value-input"
                    >
                </div>

                <button type="button" class="meta-remove" title="Remover metadado">
                    <x-lucide-trash-2 class="lucid-icon" />
                </button>
            </div>
        @endforeach
    </div>

    <x-form.button type="button" variant="secondary" class="meta-add" icon="plus">
        Adicionar meta
    </x-form.button>
</div>

@once
@push('styles')
<style>
.meta-editor .meta-pairs-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.meta-editor .meta-pair {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    width: 100%;
}

.meta-editor .meta-key-group {
    flex: 0 0 40%;
    min-width: 0;
}

.meta-editor .meta-key-group .form-field {
    margin-bottom: 0;
}

.meta-editor .meta-value-group {
    flex: 1 1 auto;
    min-width: 0;
}

.meta-editor .meta-value-input {
    width: 100%;
}

.meta-editor .meta-remove {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem;
    background: transparent;
    border: 1px solid transparent;
    border-radius: 0.375rem;
    color: #dc3545;
    cursor: pointer;
    transition: background-color 0.15s ease, border-color 0.15s ease;
    min-height: 38px;
    flex-shrink: 0;
}

.meta-editor .meta-remove:hover {
    background-color: rgba(220, 53, 69, 0.1);
    border-color: rgba(220, 53, 69, 0.2);
}

.meta-editor .meta-add {
    margin-top: 0.75rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.meta-editor').forEach(editor => {
        const name = editor.dataset.metaName;
        const list = editor.querySelector('.meta-pairs-list');
        const template = editor.querySelector('template');
        const addBtn = editor.querySelector('.meta-add');

        if (!list || !template || !addBtn) return;

        const updateIndices = () => {
            list.querySelectorAll('.meta-pair').forEach((pair, i) => {
                const select = pair.querySelector('select');
                const input = pair.querySelector('.meta-value-input');
                if (select) select.name = `${name}[${i}][key]`;
                if (input) input.name = `${name}[${i}][value]`;
            });
        };

        const createPair = () => {
            const nextIndex = list.querySelectorAll('.meta-pair').length;
            const markup = template.innerHTML
                .replace(/__NAME__/g, name)
                .replace(/__INDEX__/g, nextIndex);

            const container = document.createElement('div');
            container.innerHTML = markup.trim();
            const pairElement = container.firstElementChild;

            if (pairElement) {
                list.appendChild(pairElement);
                updateIndices();
            }
        };

        addBtn.addEventListener('click', createPair);

        list.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.meta-remove');
            if (!removeBtn) return;

            const pair = removeBtn.closest('.meta-pair');
            if (!pair) return;

            if (list.querySelectorAll('.meta-pair').length > 1) {
                pair.remove();
                updateIndices();
            } else {
                const select = pair.querySelector('select');
                const input = pair.querySelector('.meta-value-input');
                if (select) {
                    select.selectedIndex = 0;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
                if (input) {
                    input.value = '';
                }
            }
        });
    });
});
</script>
@endpush
@endonce
