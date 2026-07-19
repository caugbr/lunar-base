/**
 * Dialog JS - Utilitário autônomo para substituição de alert/confirm/prompt
 */
class Dialog {
    /**
     * Instancia e exibe o modal dinamicamente no DOM
     */
    static show({ type = 'alert', title = '', message = '', defaultValue = '', confirmText = 'Confirmar', cancelText = 'Cancelar' }) {
        return new Promise((resolve) => {
            // 1. Cria o overlay container do modal
            const overlay = document.createElement('div');
            overlay.className = 'modal-overlay';
            overlay.style.pointerEvents = 'auto';
            overlay.style.opacity = '0';
            overlay.style.transition = 'opacity 0.2s ease';

            // 2. Prepara os botões e campos específicos com as classes do dialog.css
            const isAlert = type === 'alert';
            const inputHtml = type === 'prompt'
                ? `<input type="text" id="dialog-prompt-input" class="dialog-input" value="${defaultValue}" />`
                : '';

            const footerHtml = isAlert
                ? `<button id="dialog-btn-confirm" class="dialog-btn dialog-btn-primary">${confirmText}</button>`
                : `
                    <button id="dialog-btn-cancel" class="dialog-btn dialog-btn-secondary">${cancelText}</button>
                    <button id="dialog-btn-confirm" class="dialog-btn dialog-btn-primary">${confirmText}</button>
                `;

            // 3. Monta a árvore de elementos internos
            overlay.innerHTML = `
                <div class="modal-backdrop" style="opacity: 0; transition: opacity 0.2s ease;"></div>
                <div class="modal-box sm" style="transform: scale(0.95); opacity: 0; transition: transform 0.2s ease, opacity 0.2s ease;">
                    <div class="modal-header">
                        <h3 class="modal-title">${title || (type === 'alert' ? 'Aviso' : type === 'confirm' ? 'Confirmação' : 'Entrada')}</h3>
                        <button id="dialog-btn-close" class="modal-close" aria-label="Fechar">✕</button>
                    </div>
                    <div class="modal-body">
                        <p style="margin: 0; color: #374151;">${message}</p>
                        ${inputHtml}
                    </div>
                    <div class="modal-footer">
                        ${footerHtml}
                    </div>
                </div>
            `;

            document.body.appendChild(overlay);

            // Bloqueia a rolagem do corpo da página
            document.body.style.overflow = 'hidden';

            // 4. Executa a animação de entrada no frame seguinte
            requestAnimationFrame(() => {
                overlay.style.opacity = '1';
                const backdrop = overlay.querySelector('.modal-backdrop');
                const box = overlay.querySelector('.modal-box');

                if (backdrop) backdrop.style.opacity = '1';
                if (box) {
                    box.style.opacity = '1';
                    box.style.transform = 'scale(1)';
                }

                // Direciona o foco para melhorar a acessibilidade
                if (type === 'prompt') {
                    overlay.querySelector('#dialog-prompt-input')?.focus();
                } else {
                    overlay.querySelector('#dialog-btn-confirm')?.focus();
                }
            });

            // 5. Função de encerramento e remoção com transição suave
            const closeWithResult = (result) => {
                const backdrop = overlay.querySelector('.modal-backdrop');
                const box = overlay.querySelector('.modal-box');

                if (backdrop) backdrop.style.opacity = '0';
                if (box) {
                    box.style.transform = 'scale(0.95)';
                    box.style.opacity = '0';
                }
                overlay.style.opacity = '0';

                setTimeout(() => {
                    overlay.remove();
                    // Só libera a rolagem se não restarem outros overlays ativos na página
                    if (!document.querySelector('.modal-overlay')) {
                        document.body.style.overflow = '';
                    }
                    resolve(result);
                }, 200);
            };

            // 6. Listeners para ações do usuário
            overlay.querySelector('#dialog-btn-confirm').addEventListener('click', () => {
                if (type === 'prompt') {
                    const value = overlay.querySelector('#dialog-prompt-input').value;
                    closeWithResult(value);
                } else {
                    closeWithResult(true);
                }
            });

            if (!isAlert) {
                overlay.querySelector('#dialog-btn-cancel').addEventListener('click', () => {
                    closeWithResult(type === 'prompt' ? null : false);
                });
            }

            overlay.querySelector('#dialog-btn-close').addEventListener('click', () => {
                closeWithResult(type === 'prompt' ? null : false);
            });

            overlay.querySelector('.modal-backdrop').addEventListener('click', () => {
                closeWithResult(type === 'prompt' ? null : false);
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeWithResult(type === 'prompt' ? null : false);
                }
            });

            // Atalho: Pressionar Enter no campo de texto para confirmar
            if (type === 'prompt') {
                overlay.querySelector('#dialog-prompt-input').addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        overlay.querySelector('#dialog-btn-confirm').click();
                    }
                });
            }
        });
    }

    // Métodos utilitários
    static alert(message, title = 'Aviso') {
        return this.show({ type: 'alert', title, message, confirmText: 'Ok' });
    }

    static confirm(message, title = 'Confirmação', confirmText = 'Confirmar', cancelText = 'Cancelar') {
        return this.show({ type: 'confirm', title, message, confirmText, cancelText });
    }

    static prompt(message, defaultValue = '', title = 'Entrada', confirmText = 'Confirmar', cancelText = 'Cancelar') {
        return this.show({ type: 'prompt', title, message, defaultValue, confirmText, cancelText });
    }
}

// Vincula globalmente à janela do navegador para ficar disponível
window.Dialog = Dialog;

// ==========================================================================
// Interceptadores Globais de Confirmação (data-confirm)
// ==========================================================================

document.addEventListener('DOMContentLoaded', function () {

    // 1. Intercepta o envio de formulários
    document.addEventListener('submit', async function (event) {
        const form = event.target;
        const confirmMessage = form.getAttribute('data-confirm');

        if (confirmMessage) {
            // Impede o formulário de ser enviado imediatamente
            event.preventDefault();

            // Aguarda a resposta do seu modal customizado
            const confirmed = await Dialog.confirm(confirmMessage);

            if (confirmed) {
                // Remove temporariamente o atributo para não cair no interceptador de novo
                form.removeAttribute('data-confirm');
                form.submit();
            }
        }
    });

    // 2. Intercepta cliques em links de navegação/exclusão (tags <a>)
    document.addEventListener('click', async function (event) {
        const link = event.target.closest('a[data-confirm]');

        if (link) {
            // Impede o redirecionamento imediato do link
            event.preventDefault();

            const confirmMessage = link.getAttribute('data-confirm');

            // Aguarda a resposta do seu modal customizado
            const confirmed = await Dialog.confirm(confirmMessage);

            if (confirmed) {
                window.location.href = link.href;
            }
        }
    });
});
