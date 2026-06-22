<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-shield class="lucid-icon" /> Autenticação de dois fatores</h2>
    </div>

    @if($user->hasTwoFactorEnabled())
        <p>2FA está <strong>ativo</strong>.</p>

        <form method="POST" action="{{ route('two-factor.setup') }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="admin-btn admin-btn-danger">
                <x-lucide-trash-2 class="lucid-icon" /> Desativar 2FA
            </button>
        </form>
    @else
        @if($user->twoFactorSetting && $user->twoFactorSetting->secret && !$user->twoFactorSetting->isActive())
            {{-- Setup em andamento --}}
            <p>Escaneie o QR code com seu aplicativo autenticador:</p>

            <x-qr-code :data="$qrCodeUrl" :size="$qrCodeSize" />

            <p>Ou insira manualmente esta chave:</p>
            <code>{{ $user->twoFactorSetting->secret }}</code>

            <form method="POST" action="{{ route('two-factor.setup') }}">
                @csrf

                <div class="form-group">
                    <label for="code">Código de verificação (6 dígitos)</label>
                    <input type="text" name="code" id="code" maxlength="6" required autofocus>
                    @error('code')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="buttons">
                    <button type="submit" class="admin-btn admin-btn-primary">
                        <x-lucide-check class="lucid-icon" /> Confirmar e ativar
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('two-factor.cancel') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="admin-btn admin-btn-secondary">
                    <x-lucide-x class="lucid-icon" /> Cancelar
                </button>
            </form>
        @else
            {{-- Não iniciado --}}
            <p>Adicione uma camada extra de segurança ativando a autenticação de dois fatores.</p>

            <a href="{{ route('two-factor.setup') }}" class="admin-btn admin-btn-primary">
                <x-lucide-shield class="lucid-icon" /> Ativar 2FA
            </a>
        @endif
    @endif
</div>
