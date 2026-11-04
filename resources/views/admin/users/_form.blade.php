@php
    $user = $user ?? new \App\Models\User();
    $isEdit = $user->exists;

    $roleOptions = collect($roles ?? config('rolesPermissions.roles', []))->mapWithKeys(function ($roleData, $roleKey) {
        return [$roleKey => $roleData['name'] ?? ucfirst($roleKey)];
    })->all();
@endphp

<x-form
    :action="$action"
    :method="$isEdit ? 'PUT' : 'POST'"
    id="user_form"
    :warn-changes="$isEdit"
>
    <div class="form-row">
        <x-form.text
            name="name"
            id="name"
            label="Nome"
            :value="$user->name"
            required
        />

        <x-form.text
            type="email"
            name="email"
            id="email"
            label="Email"
            :value="$user->email"
            required
        />
    </div>

    <div class="form-row">
        <x-form.password
            name="password"
            id="password"
            :label="$isEdit ? 'Nova senha' : 'Senha'"
            :required="!$isEdit"
            :help="$isEdit ? 'Deixe em branco para manter a senha atual' : null"
        />

        <x-form.select
            name="role"
            id="role"
            label="Perfil"
            :options="$roleOptions"
            :value="$user->role"
            :placeholder="false"
            required
        />
    </div>

    <div class="buttons">
        <x-form.button type="submit" variant="primary" icon="save">
            {{ $isEdit ? 'Atualizar' : 'Salvar' }}
        </x-form.button>
    </div>
</x-form>
