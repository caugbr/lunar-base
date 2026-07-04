<div x-data="formBuilder()" x-init="initOptions()" class="admin-card">
    <form method="POST" action="{{ $action }}" id="edit_form">
        @csrf
        @if(isset($form)) @method('PUT') @endif

        {{-- Metadados do Formulário --}}
        <div class="admin-card-header">
            <h2 class="group-title">
                <x-lucide-form class="lucid-icon" />
                Dados do formulário
            </h2>
            <a href="{{ route('admin.forms.index') }}" class="admin-btn admin-btn-secondary">
                <x-lucide-arrow-left class="lucid-icon" /> <span>Voltar</span>
            </a>
        </div>
        <div class="settings-group">

            <div class="admin-form-row">
                <div class="form-group">
                    <label class="field-label">Slug (URL) *</label>
                    <input type="text" name="slug" class="form-input" value="{{ old('slug', $form->slug ?? '') }}" placeholder="ex: contato" required>
                    <small class="form-help">Usado na URL e para identificar o form no código.</small>
                </div>
                <div class="form-group">
                    <label class="field-label">Título do Formulário</label>
                    <input type="text" name="title" class="form-input" value="{{ old('title', $form->title ?? '') }}">
                    <small class="form-help">Título opcional. Aparece sobre o formulário como um header H2.</small>
                </div>
            </div>

            <div class="admin-form-row">
                <div class="form-group">
                    <label class="field-label">Enviar respostas para (E-mail)</label>
                    <input type="email" name="email_to" class="form-input" value="{{ old('email_to', $form->email_to ?? '') }}" placeholder="opcional@exemplo.com">
                    <small class="form-help">Voê pode definir um email para receber as respostas (opcional).</small>
                </div>

                <div class="form-group">
                    <label class="field-label">Rótulo do Botão de Envio</label>
                    <input type="text" name="submit_button_label" class="form-input"
                        value="{{ old('submit_button_label', $form->submit_button_label ?? 'Enviar') }}"
                        placeholder="Ex: Enviar Mensagem, Solicitar Orçamento">
                    <small class="form-help">Texto que aparecerá no botão de envio do formulário.</small>
                </div>
            </div>
        </div>

        <div class="admin-form-row">
            <div class="form-group">
                <label class="field-label">Mensagem de Sucesso</label>
                <input type="text" name="submit_message" class="form-input" placeholder="Ex: Obrigado! Em breve entraremos em contato." value="{{ old('submit_message', $form->submit_message ?? 'Sua mensagem foi enviada com sucesso!') }}" />
                <small class="form-help">Mensagem exibida ao usuário após o envio do formulário.</small>
            </div>

            <div class="form-group" style="width: 280px">
                <label for="is_active">Este formulário está ativo?</label>
                <x-switch name="is_active" id="is_active" checked="{{ old('is_active', $form->is_active ?? true) }}" active="Sim" inactive="Não" />
            </div>
        </div>

        {{-- Construtor Dinâmico de Campos --}}
        <div class="settings-group" style="border-top: 2px solid #e5e7eb; padding-top: 1.5rem;">
            <div class="group-header">
                <h3 class="group-title">
                    <x-lucide-text-cursor-input class="lucid-icon" />
                    Campos do Formulário
                </h3>
                <p class="group-description">Adicione os campos que o usuário final verá.</p>
            </div>

            <template x-for="(field, index) in fields" :key="index">
                <div class="builder-field-card">

                    {{-- Ações (Topo Direito) --}}
                    <div class="builder-actions">
                        <button type="button" @click="moveFieldUp(index)" :disabled="index === 0" title="Mover para cima">
                            <x-lucide-arrow-up class="lucid-icon" />
                        </button>
                        <button type="button" @click="moveFieldDown(index)" :disabled="index === fields.length - 1" title="Mover para baixo">
                            <x-lucide-arrow-down class="lucid-icon" />
                        </button>
                        <button type="button" @click="removeField(index)" class="builder-btn-delete" title="Remover">
                            <x-lucide-trash-2 class="lucid-icon" />
                        </button>
                    </div>

                    {{-- Linha 1: Key e Tipo --}}
                    <div class="builder-grid-2">
                        <div class="form-group">
                            <label class="field-label">Key (name) *</label>
                            <input type="text" class="form-input" x-model="field.key" placeholder="ex: nome_completo">
                        </div>
                        <div class="form-group">
                            <label class="field-label">Tipo *</label>
                            <select class="form-input" x-model="field.type">
                                <option value="text">Texto Curto</option>
                                <option value="textarea">Texto Longo</option>
                                <option value="email">E-mail</option>
                                <option value="number">Número</option>
                                <option value="select">Seleção (Dropdown)</option>
                                <option value="radio">Radio (Múltipla escolha)</option>
                                <option value="checkbox">Checkbox (Múltipla escolha)</option>
                                <option value="switch">Switch (Sim/Não)</option>
                                <option value="hidden">Oculto (Hidden)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Linha 2: Label e Placeholder --}}
                    <div class="builder-grid-2">
                        <div class="form-group">
                            <label class="field-label">Label (Nome visível)</label>
                            <input type="text" class="form-input" x-model="field.label" placeholder="ex: Seu Nome">
                        </div>
                        <div class="form-group">
                            <label class="field-label">Placeholder</label>
                            <input type="text" class="form-input" x-model="field.placeholder" placeholder="Texto de ajuda dentro do campo">
                        </div>
                    </div>

                    {{-- Linha 3: Prefixo, Sufixo e Classe CSS --}}
                    <div class="builder-grid-3">
                        <div class="form-group">
                            <label class="field-label">Prefixo</label>
                            <input type="text" class="form-input" x-model="field.prefix" placeholder="ex: R$">
                        </div>
                        <div class="form-group">
                            <label class="field-label">Sufixo</label>
                            <input type="text" class="form-input" x-model="field.suffix" placeholder="ex: ,00">
                        </div>
                        <div class="form-group">
                            <label class="field-label">Classe CSS extra</label>
                            <input type="text" class="form-input" x-model="field.css_class" placeholder="ex: w-full">
                        </div>
                    </div>

                    {{-- Linha 4: Regras e Obrigatório --}}
                    <div class="builder-grid-2-1">
                        <div class="form-group">
                            <label class="field-label">Regras de Validação</label>
                            <input type="text" class="form-input" x-model="field.rules" placeholder="ex: required|string|max:255">
                            <small class="form-help">Regras do Laravel separadas por pipe (|).</small>
                        </div>
                        <div class="form-group">
                            <label class="field-label">Obrigatório?</label>
                            <div class="builder-switch-wrapper">
                                <x-switch name="req" id="req" x-model="field.required" active="Sim" inactive="Não" />
                            </div>
                        </div>
                    </div>

                    {{-- Configurações exclusivas para Hidden --}}
                    <template x-if="field.type === 'hidden'">
                        <div class="builder-number-config">
                            <div class="builder-grid">
                                <div class="form-group">
                                    <label class="field-label">Valor</label>
                                    <input type="text" class="form-input" x-model="field.value">
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Configurações exclusivas para Number --}}
                    <template x-if="field.type === 'number'">
                        <div class="builder-number-config">
                            <div class="builder-grid-3">
                                <div class="form-group">
                                    <label class="field-label">Mínimo</label>
                                    <input type="number" class="form-input" x-model="field.min">
                                </div>
                                <div class="form-group">
                                    <label class="field-label">Máximo</label>
                                    <input type="number" class="form-input" x-model="field.max">
                                </div>
                                <div class="form-group">
                                    <label class="field-label">Passo (Step)</label>
                                    <input type="number" class="form-input" x-model="field.step" placeholder="ex: 0.01">
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Configurações exclusivas para Switch --}}
                    <template x-if="field.type === 'switch'">
                        <div class="builder-grid-2">
                            <div class="form-group">
                                <label class="field-label">Texto quando Ativo</label>
                                <input type="text" class="form-input" x-model="field.active" placeholder="Ex: Sim">
                            </div>
                            <div class="form-group">
                                <label class="field-label">Texto quando Inativo</label>
                                <input type="text" class="form-input" x-model="field.inactive" placeholder="Ex: Não">
                            </div>
                        </div>
                    </template>

                    {{-- Opções (Select, Radio, Checkbox) --}}
                    <template x-if="['select', 'radio', 'checkbox'].includes(field.type)">
                        <div class="form-group">
                            <label class="field-label">Opções</label>
                            <textarea class="form-input" x-model="field.optionsText" rows="3" placeholder="sim|Sim&#10;nao|Não"></textarea>
                            <small class="form-help">Uma opção por linha. Formato: valor|Label</small>
                        </div>
                    </template>

                    <div class="form-group">
                        <label class="field-label">Descrição (Ajuda)</label>
                        <input type="text" class="form-input" x-model="field.description" placeholder="Texto pequeno abaixo do campo">
                    </div>
                </div>
            </template>
        </div>

        {{-- Input Oculto que envia o JSON para o backend --}}
        <input type="hidden" name="fields_schema" :value="JSON.stringify(getFormattedFields())">

        <div class="buttons">
            <button type="submit" class="admin-btn admin-btn-primary">
                <x-lucide-save class="lucid-icon" /> Salvar Formulário
            </button>

            <button type="button" @click="addField" class="admin-btn admin-btn-secondary">
                <x-lucide-plus class="lucid-icon" /> Adicionar Campo
            </button>
        </div>
    </form>
</div>

<x-lost-changes-warn selector="#edit_form" />

@push('scripts')
{{-- Alpine CDN --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    function formBuilder() {
        return {
            fields: @json(old('fields_schema', $form->fields_schema ?? [])),

            addField() {
                this.fields.push({
                    key: '', type: 'text', label: '', placeholder: '', description: '',
                    rules: '', required: false, optionsText: '',
                    prefix: '', suffix: '', css_class: '',
                    min: '', max: '', step: '',
                    active: 'Sim', inactive: 'Não',
                    value: ''
                });
                setTimeout(() => {
                    const last = document.querySelector('.builder-field-card:last-child');
                    if (last) {
                        last.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        last.querySelector('[x-model="field.key"]').focus();
                    }
                }, 100);
            },

            removeField(index) {
                this.fields.splice(index, 1);
            },

            moveFieldUp(index) {
                if (index > 0) {
                    let temp = this.fields[index];
                    this.fields[index] = this.fields[index - 1];
                    this.fields[index - 1] = temp;
                }
            },

            moveFieldDown(index) {
                if (index < this.fields.length - 1) {
                    let temp = this.fields[index];
                    this.fields[index] = this.fields[index + 1];
                    this.fields[index + 1] = temp;
                }
            },

            initOptions() {
                this.fields = this.fields.map(f => {
                    if (f.options && typeof f.options === 'object') {
                        f.optionsText = Object.entries(f.options).map(([k, v]) => `${k}|${v}`).join('\n');
                    } else if (!f.optionsText) {
                        f.optionsText = '';
                    }
                    return f;
                });
            },

            getFormattedFields() {
                return this.fields.map(f => {
                    let field = { ...f };

                    if (field.required && !field.rules.includes('required')) {
                        field.rules = field.rules ? 'required|' + field.rules : 'required';
                    }

                    if (['select', 'radio', 'checkbox'].includes(f.type) && f.optionsText) {
                        let options = {};
                        f.optionsText.split('\n').forEach(line => {
                            let parts = line.split('|');
                            let val = parts[0].trim();
                            let label = parts[1] ? parts[1].trim() : val;
                            if(val) options[val] = label;
                        });
                        field.options = options;
                    }

                    delete field.optionsText;
                    delete field.required;

                    // Remove chaves vazias para não poluir o JSON
                    return Object.fromEntries(Object.entries(field).filter(([_, v]) => v !== '' && v !== null));
                }).filter(f => f.key !== '');
            }
        }
    }
</script>
@endpush

@push('styles')
<style>
/* ===== Form Builder Layout ===== */
.builder-field-card {
    background: #f9fafb;
    border: 1px solid #d1d5db;
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 0.375rem;
    position: relative;
}

.builder-actions {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    display: flex;
    gap: 0.25rem;
}

.builder-actions button {
    background: none;
    border: none;
    cursor: pointer;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.2s;
}

.builder-actions button:hover:not(:disabled) {
    background: #e5e7eb;
    color: #374151;
}

.builder-actions button:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.builder-btn-delete {
    color: #ef4444 !important;
}

.builder-btn-delete:hover {
    background: #fee2e2 !important;
    color: #dc2626 !important;
}

/* Grids do Builder */
.builder-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.builder-grid-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.builder-grid-2-1 {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.builder-number-config {
    background: #f3f4f6;
    padding: 0.75rem;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
}

.builder-switch-wrapper {
    margin-top: 0.5rem;
}
</style>
@endpush
