// PersistentDialog.js - Classe separada que usa a Dialog
class PersistentDialog {
  static handlers = new Map();

  static init() {
    // Escuta eventos da Dialog
    Dialog.on('close', (data) => {
      const handler = this.handlers.get(data.id);
      if (!handler) return;

      // Calcula valor "natural" baseado no tipo
      let shouldClose;
      if (data.type === 'alert') {
        shouldClose = data.checked;
      } else if (data.type === 'confirm') {
        shouldClose = data.result === true;
      } else if (data.type === 'prompt') {
        shouldClose = data.result !== null && data.result !== '';
      }

      // Chama callback se definido
      if (handler.callback && typeof window[handler.callback] === 'function') {
        window[handler.callback](data.result);
      }

      // Aplica filtro onCloseModal se definido
      if (handler.onCloseModal && typeof window[handler.onCloseModal] === 'function') {
        shouldClose = window[handler.onCloseModal](shouldClose);
      }

      // Salva no storage se deve fechar
      if (shouldClose) {
        const storage = handler.persist ? localStorage : sessionStorage;
        storage.setItem(`dialog_persistent_${data.id}`, 'true');
      }

      // Remove handler
      this.handlers.delete(data.id);
    });
  }

  static async persistentAlert(key, message, options = {}) {
    const {
      title = 'Aviso',
      persist = false,
      callback = null,
      onCloseModal = null,
      checkboxText = 'Não mostrar novamente'
    } = options;

    const storage = persist ? localStorage : sessionStorage;
    if (storage.getItem(`dialog_persistent_${key}`) === 'true') return;

    // Registra handler
    this.handlers.set(key, { callback, onCloseModal, persist });

    // Mostra modal
    await Dialog.show({
      type: 'alert',
      title,
      message,
      id: key,
      showCheckbox: true,
      checkboxText,
      confirmText: 'Ok'
    });
  }

  static async persistentConfirm(key, message, options = {}) {
    const {
      title = 'Confirmação',
      persist = false,
      callback = null,
      onCloseModal = null,
      checkboxText = 'Não perguntar novamente',
      confirmText = 'Confirmar',
      cancelText = 'Cancelar'
    } = options;

    const storage = persist ? localStorage : sessionStorage;
    if (storage.getItem(`dialog_persistent_${key}`) === 'true') return;

    this.handlers.set(key, { callback, onCloseModal, persist });

    await Dialog.show({
      type: 'confirm',
      title,
      message,
      id: key,
      showCheckbox: true,
      checkboxText,
      confirmText,
      cancelText
    });
  }

  static async persistentPrompt(key, message, options = {}) {
    const {
      title = 'Entrada',
      defaultValue = '',
      persist = false,
      callback = null,
      onCloseModal = null,
      checkboxText = 'Não perguntar novamente',
      confirmText = 'Confirmar',
      cancelText = 'Cancelar'
    } = options;

    const storage = persist ? localStorage : sessionStorage;
    if (storage.getItem(`dialog_persistent_${key}`) === 'true') return;

    this.handlers.set(key, { callback, onCloseModal, persist });

    await Dialog.show({
      type: 'prompt',
      title,
      message,
      defaultValue,
      id: key,
      showCheckbox: true,
      checkboxText,
      confirmText,
      cancelText
    });
  }

  static reset(key, persist = false) {
    const storage = persist ? localStorage : sessionStorage;
    storage.removeItem(`dialog_persistent_${key}`);
  }

  static hasBeenSeen(key, persist = false) {
    const storage = persist ? localStorage : sessionStorage;
    return storage.getItem(`dialog_persistent_${key}`) === 'true';
  }
}

window.PersistentDialog = PersistentDialog;

// Inicializa automaticamente
PersistentDialog.init();
