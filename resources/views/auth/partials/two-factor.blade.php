<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-user-lock class="lucid-icon" /> Autenticação de dois fatores</h2>
    </div>

    @if($user->hasTwoFactorEnabled())
        <p>2FA está <strong>ativo</strong>.</p>

        <form method="POST" action="{{ route('admin.users.two-factor.disable', $user->id) }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="admin-btn admin-btn-danger" onclick="return confirm('Desativar 2FA?')">
                <x-lucide-lock-open class="lucid-icon" /> Desativar 2FA
            </button>
        </form>
    @else
        @if($user->twoFactorSetting && $user->twoFactorSetting->secret && !$user->twoFactorSetting->isActive())
            {{-- Setup em andamento --}}
            <p>Escaneie o QR code com seu aplicativo autenticador:</p>

            <x-qr-code :data="$qrCodeUrl" :size="$qrCodeSize" />

            <p>Ou insira manualmente esta chave:</p>
            <code class="manual-2fa-code">{{ $user->twoFactorSetting->secret }}</code>

            <form method="POST" action="{{ route('two-factor.setup') }}" id="enable-2fa">
                @csrf

                <div class="form-group">
                    <label for="code">Código de verificação (6 dígitos)</label>
                    <input type="text" name="code" id="code" maxlength="6" required autofocus>
                    @error('code')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>
            </form>

            <form method="POST" action="{{ route('two-factor.cancel') }}" id="disable-2fa">
                @csrf
                @method('DELETE')
            </form>

            <div class="buttons">
                <button type="submit" form="enable-2fa" class="admin-btn admin-btn-primary">
                    <x-lucide-check class="lucid-icon" /> Confirmar e ativar
                </button>
                <button type="submit" form="disable-2fa" class="admin-btn admin-btn-secondary">
                    <x-lucide-x class="lucid-icon" /> Cancelar
                </button>
            </div>
        @else
            {{-- Não iniciado --}}
            <p>Adicione uma camada extra de segurança ativando a autenticação de dois fatores.</p>

            <div class="buttons">
                <a href="{{ route('two-factor.setup') }}" class="admin-btn admin-btn-primary">
                    <x-lucide-lock class="lucid-icon" /> Ativar 2FA
                </a>
            </div>
        @endif
    @endif
</div>

<style>
    #code {
        width: 10em;
    }
    .manual-2fa-code {
        border: 1px solid #ddd;
        padding: 6px 12px;
        background-color: #ededed;
        margin-bottom: 1.5rem;
        display: inline-block;
    }
</style>
