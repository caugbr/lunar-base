<div class="admin-card" style="margin-top: 24px;">
    <div class="admin-card-header">
        <h2><x-lucide-image class="lucid-icon" /> Foto de Perfil</h2>
    </div>

    <div class="avatar-manager-layout">
        <!-- Visualização Atual -->
        <div class="avatar-current-preview">
            <img src="{{ \Plugins\Avatars\Helpers\AvatarHelper::getUrl($user) }}" alt="Minha foto" class="avatar-large-circle" />
        </div>

        <!-- Formulário de Ações -->
        <div class="avatar-actions-form">
            <p style="font-size: 0.875rem; color: var(--color-text-muted); margin-top: 0; margin-bottom: 1.5rem;">
                Escolha uma imagem de até 5MB. Ela será recortada automaticamente em um quadrado perfeito e otimizada para carregamento rápido.
            </p>

            <form action="{{ route('admin.profile.avatar.update') }}" method="POST" enctype="multipart/form-data" id="avatar_form">
                @csrf
                <div class="form-group">
                    <x-upload-area name="avatar" accept="image/*" buttonLabel="Escolher Imagem" message="Solte a imagem aqui para atualizar" />
                </div>

                <div class="buttons" style="margin-top: 1.5rem; display: flex; justify-content: flex-start; gap: 0.75rem;">
                    <button type="submit" class="admin-btn admin-btn-primary">
                        <x-lucide-upload class="lucid-icon" /> Salvar Foto
                    </button>

                    @if(\Illuminate\Support\Facades\Storage::disk('public')->exists("avatars/{$user->id}.webp"))
                        <button type="button"
                                onclick="(async () => { if(await Dialog.confirm('Deseja remover sua foto de perfil?')) document.getElementById('delete_avatar_form').submit(); })()"
                                class="admin-btn admin-btn-danger">
                            <x-lucide-trash-2 class="lucid-icon" /> Remover Foto
                        </button>
                    @endif
                </div>
            </form>

            <form id="delete_avatar_form" action="{{ route('admin.profile.avatar.destroy') }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/avatars/css/avatars.css') }}">
@endpush
@endonce
