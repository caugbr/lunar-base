// Dialog.js - Classe base com sistema de eventos
class Dialog {
  static listeners = new Map();

  // Sistema de eventos interno
  static on(event, callback) {
    if (!this.listeners.has(event)) {
      this.listeners.set(event, []);
    }
    this.listeners.get(event).push(callback);
  }

  static emit(event, data) {
    if (this.listeners.has(event)) {
      this.listeners.get(event).forEach(cb => cb(data));
    }
  }

  static show({ type = 'alert', title = '', message = '', defaultValue = '',
                confirmText = 'Confirmar', cancelText = 'Cancelar',
                id = null, showCheckbox = false, checkboxText = 'Não mostrar novamente' }) {
    return new Promise((resolve) => {
      const overlay = document.createElement('div');
      overlay.className = 'dialog-modal-overlay';
      overlay.style.pointerEvents = 'auto';
      overlay.style.opacity = '0';
      overlay.style.transition = 'opacity 0.2s ease';

      const isAlert = type === 'alert';
      const inputHtml = type === 'prompt'
          ? `<input type="text" id="dialog-prompt-input" class="dialog-input" value="${defaultValue}" />`
          : '';

      const checkboxHtml = showCheckbox
          ? `<label style="display: flex; align-items: center; margin-top: 12px; cursor: pointer;">
               <input type="checkbox" id="dialog-checkbox" style="margin-right: 8px;" />
               <span style="color: #6b7280; font-size: 14px;">${checkboxText}</span>
             </label>`
          : '';

      const footerHtml = isAlert
          ? `<button id="dialog-btn-confirm" class="dialog-btn dialog-btn-primary">${confirmText}</button>`
          : `
              <button id="dialog-btn-cancel" class="dialog-btn dialog-btn-secondary">${cancelText}</button>
              <button id="dialog-btn-confirm" class="dialog-btn dialog-btn-primary">${confirmText}</button>
            `;

      overlay.innerHTML = `
          <div class="dialog-modal-backdrop" style="opacity: 0; transition: opacity 0.2s ease;"></div>
          <div class="dialog-modal-box sm" style="transform: scale(0.95); opacity: 0; transition: transform 0.2s ease, opacity 0.2s ease;">
              <div class="dialog-modal-header">
                  <h3 class="dialog-modal-title">${title || (type === 'alert' ? 'Aviso' : type === 'confirm' ? 'Confirmação' : 'Entrada')}</h3>
                  <button id="dialog-btn-close" class="dialog-modal-close" aria-label="Fechar">✕</button>
              </div>
              <div class="dialog-modal-body">
                  <p style="margin: 0; color: #374151;">${message}</p>
                  ${inputHtml}
                  ${checkboxHtml}
              </div>
              <div class="dialog-modal-footer">
                  ${footerHtml}
              </div>
          </div>
      `;

      document.body.appendChild(overlay);
      document.body.style.overflow = 'hidden';

      requestAnimationFrame(() => {
          overlay.style.opacity = '1';
          const backdrop = overlay.querySelector('.dialog-modal-backdrop');
          const box = overlay.querySelector('.dialog-modal-box');
          if (backdrop) backdrop.style.opacity = '1';
          if (box) {
              box.style.opacity = '1';
              box.style.transform = 'scale(1)';
          }
          if (type === 'prompt') {
              overlay.querySelector('#dialog-prompt-input')?.focus();
          } else {
              overlay.querySelector('#dialog-btn-confirm')?.focus();
          }
      });

      this.emit('open', { type, message });

      const closeWithResult = (result) => {
          const backdrop = overlay.querySelector('.dialog-modal-backdrop');
          const box = overlay.querySelector('.dialog-modal-box');
          if (backdrop) backdrop.style.opacity = '0';
          if (box) {
              box.style.transform = 'scale(0.95)';
              box.style.opacity = '0';
          }
          overlay.style.opacity = '0';

          setTimeout(() => {
              overlay.remove();
              if (!document.querySelector('.dialog-modal-overlay')) {
                  document.body.style.overflow = '';
              }

              // Captura estado do checkbox se existir
              const checkbox = overlay.querySelector('#dialog-checkbox');
              const checked = checkbox ? checkbox.checked : false;

              // Dispara evento de fechamento
              this.emit('close', { id, type, result, checked });

              resolve(result);
          }, 200);
      };

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

      overlay.querySelector('.dialog-modal-backdrop').addEventListener('click', () => {
          closeWithResult(type === 'prompt' ? null : false);
      });

      document.addEventListener('keydown', (e) => {
          if (e.key === 'Escape') {
              closeWithResult(type === 'prompt' ? null : false);
          }
      });

      if (type === 'prompt') {
          overlay.querySelector('#dialog-prompt-input').addEventListener('keydown', (e) => {
              if (e.key === 'Enter') {
                  overlay.querySelector('#dialog-btn-confirm').click();
              }
          });
      }
    });
  }

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

window.Dialog = Dialog;
