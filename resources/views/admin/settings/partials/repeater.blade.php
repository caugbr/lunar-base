@php
    $rows = old($def['key'], $value ?? []);
    if (is_string($rows)) {
        $rows = json_decode($rows, true) ?? [];
    }
    if (!is_array($rows)) {
        $rows = [];
    }
    $subFields = $def['fields'] ?? [];
    $repeaterKey = $def['key'];
@endphp

<div class="repeater-editor" data-repeater-key="{{ $repeaterKey }}">
    <div class="table-wrap">
        <table class="admin-table repeater-table">
            <thead>
                <tr>
                    @foreach($subFields as $subDef)
                        <th>{{ $subDef['label'] ?? ucfirst($subDef['key']) }}</th>
                    @endforeach
                    <th class="col-actions">Ações</th>
                </tr>
            </thead>
            <tbody class="repeater-rows-list">
                @foreach($rows as $index => $row)
                    <tr class="repeater-row">
                        @foreach($subFields as $subDef)
                            @php
                                $subKey = $subDef['key'];
                                $subName = "{$repeaterKey}[{$index}][{$subKey}]";
                                $subVal = $row[$subKey] ?? ($subDef['default'] ?? '');
                            @endphp
                            <td>
                                @include('admin.settings.partials.input', [
                                    'def' => $subDef,
                                    'name' => $subName,
                                    'value' => $subVal,
                                    'withLabel' => false
                                ])
                            </td>
                        @endforeach
                        <td class="col-actions">
                            <button type="button" class="transparent-btn repeater-remove" title="Remover linha">
                                <x-lucide-trash-2 class="lucid-icon" />
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Template HTML para inserção de novas linhas via JS --}}
    <template class="repeater-template">
        <tr class="repeater-row">
            @foreach($subFields as $subDef)
                @php
                    $subKey = $subDef['key'];
                    $subName = "{$repeaterKey}[__INDEX__][{$subKey}]";
                    $subVal = $subDef['default'] ?? '';
                @endphp
                <td>
                    @include('admin.settings.partials.input', [
                        'def' => $subDef,
                        'name' => $subName,
                        'value' => $subVal,
                        'withLabel' => false
                    ])
                </td>
            @endforeach
            <td class="col-actions">
                <button type="button" class="transparent-btn repeater-remove" title="Remover linha">
                    <x-lucide-trash-2 class="lucid-icon" />
                </button>
            </td>
        </tr>
    </template>

    <div class="repeater-footer">
        <x-form.button type="button" variant="secondary" icon="plus" class="repeater-add">
            Adicionar item
        </x-form.button>
    </div>
</div>

@once
@push('styles')
<style>
    .repeater-editor .col-actions {
        width: 60px;
        text-align: center;
        vertical-align: middle;
    }

    .repeater-editor .repeater-remove {
        color: #dc3545;
        padding: 0.25rem 0.5rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .repeater-editor .repeater-remove:hover {
        background-color: rgba(220, 53, 69, 0.1);
        border-radius: 4px;
    }

    .repeater-editor .repeater-footer {
        display: flex;
        justify-content: flex-end;
        margin-top: 0.75rem;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.repeater-editor').forEach(editor => {
        const key = editor.dataset.repeaterKey;
        const list = editor.querySelector('.repeater-rows-list');
        const template = editor.querySelector('template.repeater-template');
        const addBtn = editor.querySelector('.repeater-add');

        if (!list || !template || !addBtn) return;

        const updateIndices = () => {
            list.querySelectorAll('.repeater-row').forEach((row, i) => {
                // Atualiza atributos name
                row.querySelectorAll('[name]').forEach(input => {
                    const pattern = new RegExp(`${key}\\[(\\d+|__INDEX__)\\]`, 'g');
                    input.name = input.name.replace(pattern, `${key}[${i}]`);
                });

                // Atualiza atributos id e for para evitar duplicidade de nós no DOM
                row.querySelectorAll('[id]').forEach(el => {
                    const pattern = new RegExp(`${key}_(\\d+|__INDEX__)_`, 'g');
                    el.id = el.id.replace(pattern, `${key}_${i}_`);
                });

                row.querySelectorAll('label[for]').forEach(lbl => {
                    const pattern = new RegExp(`${key}_(\\d+|__INDEX__)_`, 'g');
                    lbl.htmlFor = lbl.htmlFor.replace(pattern, `${key}_${i}_`);
                });
            });
        };

        addBtn.addEventListener('click', () => {
            const count = list.querySelectorAll('.repeater-row').length;
            const markup = template.innerHTML
                .replace(/__INDEX__/g, count);

            const container = document.createElement('tbody');
            container.innerHTML = markup.trim();
            const newRow = container.firstElementChild;

            if (newRow) {
                list.appendChild(newRow);
                updateIndices();
            }
        });

        list.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.repeater-remove');
            if (!removeBtn) return;

            const row = removeBtn.closest('.repeater-row');
            if (row) {
                row.remove();
                updateIndices();
            }
        });
    });
});
</script>
@endpush
@endonce
