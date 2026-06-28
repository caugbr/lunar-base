<div class="admin-card">
    <div class="admin-card-header">
        <h2><x-lucide-user-lock class="lucid-icon" /> Autenticação de duas etapas</h2>
    </div>

    @if($user->hasTwoFactorEnabled())
        {{-- ESTADO 1: ATIVADO --}}
        <p>2FA está <strong>ativo</strong>.</p>
        <form method="POST" action="{{ route('admin.users.two-factor.disable', $user->id) }}">
            @csrf @method('DELETE')
            <button type="submit" class="admin-btn admin-btn-danger" onclick="return confirm('Desativar 2FA?')">
                <x-lucide-lock-open class="lucid-icon" /> Desativar 2FA
            </button>
        </form>

    @elseif($user->twoFactorSetting && $user->twoFactorSetting->secret && !$user->twoFactorSetting->isActive())
        {{-- ESTADO 2: SETUP EM ANDAMENTO --}}
        <p>Escaneie o QR code com seu aplicativo autenticador:</p>
        <x-qr-code :data="$qrCodeUrl" :size="$qrCodeSize" />
        <p>Ou insira manualmente esta chave: <code>{{ $user->twoFactorSetting->secret }}</code></p>

        <form method="POST" action="{{ route('two-factor.setup') }}" id="enable-2fa">
            @csrf
            <div class="form-group">
                <label for="code">Código (App ou E-mail):</label>
                <input type="text" name="code" id="code" maxlength="6" required>
            </div>
        </form>

        {{-- BOTÃO PARA RECEBER POR EMAIL CASO O QR FALHE --}}
        <form method="POST" action="{{ route('two-factor.setup-email-trigger') }}">
            @csrf
            <button type="submit" class="admin-btn admin-btn-secondary">
                <x-lucide-mail class="lucid-icon" />
                Não conseguiu ler? Enviar código por e-mail
            </button>
        </form>

            <form method="POST" action="{{ route('two-factor.cancel') }}" id="disable-2fa">
                @csrf
                @method('DELETE')
            </form>

        <div class="buttons">
            <button type="submit" form="enable-2fa" class="admin-btn admin-btn-primary">
                <x-lucide-check class="lucid-icon" />
                Confirmar
            </button>
            <button type="submit" form="disable-2fa" class="admin-btn admin-btn-secondary">
                <x-lucide-x class="lucid-icon" /> Cancelar
            </button>
        </div>

    @else
        {{-- ESTADO 3: NÃO INICIADO --}}
        <p>Adicione uma camada extra de segurança.</p>
        <div class="buttons">
            <div style="display: flex; gap: 1.5rem;">
                <a href="{{ route('two-factor.setup') }}" class="admin-btn admin-btn-primary">
                    <x-lucide-lock class="lucid-icon" /> Ativar via App
                </a>

                {{-- EMAIL --}}
                <form method="POST" action="{{ route('two-factor.setup-email-trigger') }}">
                    @csrf
                    <button type="submit" class="admin-btn admin-btn-secondary">
                        <x-lucide-mail class="lucid-icon" /> Ativar via E-mail
                    </button>
                </form>
            </div>
        </div>
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
