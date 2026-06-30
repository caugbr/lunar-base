@props([
    'name' => 'meta',
    'existingKeys' => [],
    'values' => [],
])

@php
// $values vem como ['meta_key' => 'meta_value', ...]
$pairs = [];
foreach ($values as $key => $val) {
    $pairs[] = ['key' => $key, 'value' => $val];
}
// Se vazio, começa com um par vazio
if (empty($pairs)) {
    $pairs[] = ['key' => '', 'value' => ''];
}
@endphp

<div class="meta-editor" id="{{ $name }}_editor" data-meta-name="{{ $name }}">
    <template id="{{ $name }}_template">
        <div class="meta-pair">
            <div class="form-group">
                <x-select-input
                    :name="'__NAME__[__INDEX__][key]'"
                    :options="$existingKeys"
                    placeholder="-- Selecione ou insira --"
                    :allowInsert="true"
                    insertLabel="Inserir nova chave..."
                    insertPlaceholder="Nova chave"
                />
            </div>
            <div class="form-group" style="flex: 1">
                <input type="text"
                       name="__NAME__[__INDEX__][value]"
                       placeholder="Valor"
                       class="meta-value-input">
            </div>
            <button type="button" class="meta-remove" title="Remover">
                <x-lucide-trash-2 class="lucid-icon" />
            </button>
        </div>
    </template>

    <div class="meta-pairs-list">
        @foreach($pairs as $index => $pair)
            <div class="meta-pair">
                <div class="form-group">
                    <input type="text"
                           name="{{ $name }}[{{ $index }}][key]"
                           value="{{ $pair['key'] }}"
                           readonly>
                </div>
                <div class="form-group" style="flex: 1">
                    <input type="text"
                        name="{{ $name }}[{{ $index }}][value]"
                        value="{{ $pair['value'] }}"
                        placeholder="Valor"
                        class="meta-value-input">
                </div>
                <button type="button" class="meta-remove" title="Remover">
                    <x-lucide-trash-2 class="lucid-icon" />
                </button>
            </div>
        @endforeach
    </div>

    <button type="button" class="admin-btn admin-btn-secondary meta-add">
        <x-lucide-plus class="lucid-icon" /> Adicionar meta
    </button>
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
}
.meta-editor .meta-pair .form-group {
    margin-bottom: 0;
    flex: 0 0 40%;
}
.meta-editor .meta-pair .meta-value-input {
    flex: 1;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border-color, #d1d5db);
    border-radius: 0.375rem;
    font-size: 0.875rem;
    min-height: 38px;
}
.meta-editor .meta-remove {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem;
    background: transparent;
    border: 1px solid transparent;
    border-radius: 0.375rem;
    color: #ef4444;
    cursor: pointer;
    transition: all 0.15s;
    min-height: 38px;
    /* margin-top: 1.5rem; */
}
.meta-editor .meta-remove:hover {
    background: #fef2f2;
    border-color: #fecaca;
}
.meta-editor .meta-add {
    margin-top: 0.75rem;
}
</style>
@endpush

@push('scripts')
<script>
(function() {
    document.querySelectorAll('.meta-editor').forEach(editor => {
        const name = editor.dataset.metaName;
        const list = editor.querySelector('.meta-pairs-list');
        const template = editor.querySelector('template');
        const addBtn = editor.querySelector('.meta-add');

        let index = list.querySelectorAll('.meta-pair').length;

        function updateIndices() {
            list.querySelectorAll('.meta-pair').forEach((pair, i) => {
                const select = pair.querySelector('select');
                const input = pair.querySelector('.meta-value-input');
                if (select) select.name = `${name}[${i}][key]`;
                if (input) input.name = `${name}[${i}][value]`;
            });
            index = list.querySelectorAll('.meta-pair').length;
        }

        function createPair() {
            const html = template.innerHTML
                .replace(/__NAME__/g, name)
                .replace(/__INDEX__/g, index);
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            const pair = wrapper.firstElementChild;

            // Re-inicializa o select-input do par novo
            const selectWrapper = pair.querySelector('.form-group[id$="_wrapper"]');
            if (selectWrapper) {
                const select = selectWrapper.querySelector('select');
                const input = selectWrapper.querySelector('input[id$="_input"]');

                const undo = () => {
                    if (!selectWrapper.hasAttribute('data-insert-mode')) return;
                    selectWrapper.removeAttribute('data-insert-mode');
                    if (input.value) {
                        let exists = false;
                        for (let opt of select.options) {
                            if (opt.value === input.value) {
                                exists = true;
                                select.value = opt.value;
                                break;
                            }
                        }
                        if (!exists) {
                            const option = document.createElement('option');
                            option.value = input.value;
                            option.textContent = input.value;
                            option.selected = true;
                            select.insertBefore(option, select.lastElementChild);
                        }
                    }
                    input.value = '';
                    select.focus();
                };

                const cancel = () => {
                    if (!selectWrapper.hasAttribute('data-insert-mode')) return;
                    selectWrapper.removeAttribute('data-insert-mode');
                    input.value = '';
                    select.selectedIndex = 0;
                    select.focus();
                };

                select.addEventListener('change', function() {
                    if (this.value === '__insert__') {
                        selectWrapper.setAttribute('data-insert-mode', '');
                        setTimeout(() => input.focus(), 80);
                    } else {
                        selectWrapper.removeAttribute('data-insert-mode');
                    }
                });

                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') { e.preventDefault(); undo(); }
                    if (e.key === 'Escape') { e.preventDefault(); cancel(); }
                });

                document.addEventListener('click', (e) => {
                    if (!selectWrapper.contains(e.target)) cancel();
                });
            }

            list.appendChild(pair);
            updateIndices();
        }

        addBtn.addEventListener('click', createPair);

        list.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.meta-remove');
            if (!removeBtn) return;

            const pair = removeBtn.closest('.meta-pair');
            if (list.querySelectorAll('.meta-pair').length > 1) {
                pair.remove();
                updateIndices();
            } else {
                // Limpa em vez de remover o último
                const select = pair.querySelector('select');
                const input = pair.querySelector('.meta-value-input');
                if (select) { select.value = ''; select.selectedIndex = 0; }
                if (input) input.value = '';
            }
        });
    });
})();
</script>
@endpush
@endonce
