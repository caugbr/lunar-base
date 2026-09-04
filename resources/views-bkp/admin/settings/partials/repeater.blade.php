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
                    <th style="width: 50px; text-align: center;">Ações</th>
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
                                    'value' => $subVal
                                ])
                            </td>
                        @endforeach
                        <td style="text-align: center; vertical-align: middle;">
                            <button type="button" class="transparent-btn repeater-remove" title="Remover linha" style="color: #ef4444;">
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
                        'value' => $subVal
                    ])
                </td>
            @endforeach
            <td style="text-align: center; vertical-align: middle;">
                <button type="button" class="transparent-btn repeater-remove" title="Remover linha" style="color: #ef4444;">
                    <x-lucide-trash-2 class="lucid-icon" />
                </button>
            </td>
        </tr>
    </template>

    <button type="button" class="admin-btn admin-btn-secondary repeater-add" style="margin-top: 0.75rem; float: right;">
        <x-lucide-plus class="lucid-icon" /> Adicionar Item
    </button>
</div>

@once
@push('scripts')
<script>
(function() {
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.repeater-editor').forEach(editor => {
            const key = editor.dataset.repeaterKey;
            const list = editor.querySelector('.repeater-rows-list');
            const template = editor.querySelector('template.repeater-template');
            const addBtn = editor.querySelector('.repeater-add');

            function updateIndices() {
                list.querySelectorAll('.repeater-row').forEach((row, i) => {
                    row.querySelectorAll('[name]').forEach(input => {
                        // Atualiza os atributos name do tipo extra_domains[0][field]
                        const newName = input.name.replace(new RegExp(`${key}\\[(\\d+|__INDEX__)\\]`, 'g'), `${key}[${i}]`);
                        input.name = newName;
                    });
                });
            }

            if (addBtn && template && list) {
                addBtn.addEventListener('click', () => {
                    const count = list.querySelectorAll('.repeater-row').length;
                    const html = template.innerHTML.replace(/__INDEX__/g, count);
                    const wrapper = document.createElement('tbody');
                    wrapper.innerHTML = html.trim();
                    const newRow = wrapper.firstElementChild;
                    list.appendChild(newRow);
                    updateIndices();
                });
            }

            if (list) {
                list.addEventListener('click', e => {
                    const removeBtn = e.target.closest('.repeater-remove');
                    if (!removeBtn) return;
                    const row = removeBtn.closest('.repeater-row');
                    if (row) {
                        row.remove();
                        updateIndices();
                    }
                });
            }
        });
    });
})();
</script>
@endpush
@endonce
