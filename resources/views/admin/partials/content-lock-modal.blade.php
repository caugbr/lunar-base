<!-- Container do Content Lock (Dados + Modal + Script) -->
<div
    id="content-lock-modal"
    data-lock-type="{{ $lockData['type'] }}"
    data-lock-id="{{ $lockData['id'] }}"
    style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); z-index: 99998; align-items: center; justify-content: center; padding: 16px;">

    <div style="background: #fff; border-radius: 8px; max-width: 480px; width: 100%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <h3 style="margin-top: 0; color: #1e293b; font-size: 18px;">Conteúdo em Edição</h3>
        <p style="color: #64748b; font-size: 14px; line-height: 1.5;">
            <strong id="lock-user-name" style="color: #0f172a;">Outro usuário</strong> está editando este conteúdo no momento.
        </p>
        <p style="color: #64748b; font-size: 13px;">
            Para evitar que alterações sejam sobrescritas, o formulário foi desativado. Você pode voltar para a listagem ou assumir o controle da edição.
        </p>

        <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
            <a href="javascript:history.back()" style="padding: 8px 16px; border: 1px solid #cbd5e1; border-radius: 6px; text-decoration: none; color: #475569; font-size: 14px;">
                Voltar
            </a>
            <button id="btn-lock-takeover" type="button" style="padding: 8px 16px; background: #ef4444; border: none; border-radius: 6px; color: #fff; font-size: 14px; cursor: pointer;">
                Assumir o controle
            </button>
        </div>
    </div>
</div>

<script src="{{ asset('js/content-lock.js') }}"></script>
