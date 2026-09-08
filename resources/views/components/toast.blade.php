{{-- resources/views/components/toast.blade.php --}}
<div
    x-data="{
        get items() { return window.Alpine && Alpine.store('toasts') ? Alpine.store('toasts').items : [] },
        posStyle() { return window.Alpine && Alpine.store('toasts') ? Alpine.store('toasts').positionStyle : 'top: 20px; right: 20px;' }
    }"
    class="lunar-toast-container"
    :style="posStyle()"
    role="region"
    aria-label="Notificações"
>
    <template x-for="toast in items" :key="toast.id">
        <div
            @mouseenter="$store.toasts.pauseTimer(toast)"
            @mouseleave="$store.toasts.resumeTimer(toast)"
            class="lunar-toast-item"
            :class="'lunar-toast-' + toast.type + (toast.closing ? ' is-closing' : '')"
        >
            <!-- Ícone -->
            <div class="lunar-toast-icon">
                <template x-if="toast.type === 'success'">
                    <svg class="text-success" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                </template>
                <template x-if="toast.type === 'error'">
                    <svg class="text-error" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                </template>
                <template x-if="toast.type === 'warning' || toast.type === 'warn'">
                    <svg class="text-warning" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </template>
                <template x-if="toast.type === 'info'">
                    <svg class="text-info" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </template>
            </div>

            <!-- Conteúdo -->
            <div class="lunar-toast-content">
                <h4 x-show="toast.title" x-text="toast.title" class="lunar-toast-title"></h4>
                <p x-text="toast.message" class="lunar-toast-message"></p>
            </div>

            <!-- Botão Fechar -->
            <button @click="$store.toasts.remove(toast.id)" class="lunar-toast-close" aria-label="Fechar">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </template>
</div>

<style>
    /* Container Global Fixo */
    .lunar-toast-container {
        position: fixed;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
        max-width: 380px;
        width: 100%;
        padding: 16px;
        overflow: hidden;
    }

    /* Item do Toast com Efeito Slide Puro (Gaveta) */
    .lunar-toast-item {
        position: relative;
        pointer-events: auto;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px;
        border-radius: 6px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        background: var(--color-bg-card, #ffffff);
        border-left: 4px solid transparent;

        /* Estado inicial ao nascer (fora da janela, à direita) */
        opacity: 1;
        transform: translateX(110%);
        animation: lunarSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    /* Estado quando o toast está indo embora (escorregando para fora) */
    .lunar-toast-item.is-closing {
        /* animation: lunarSlideOut 0.3s cubic-bezier(0.4, 0, 1, 1) forwards; */
        animation: lunarSlideOut 0.7s cubic-bezier(0.4, 0, 1, 1) forwards;
    }

    /* Keyframes de Entrada (De fora para dentro) */
    @keyframes lunarSlideIn {
        from {
            transform: translateX(110%);
        }
        to {
            transform: translateX(0);
        }
    }

    /* Keyframes de Saída (De dentro para fora) */
    @keyframes lunarSlideOut {
        0% {
            transform: translateX(0);
            max-height: 100px;
            margin-bottom: 0px;
        }
        50% {
            transform: translateX(110%);
            max-height: 100px;
            margin-bottom: 0px;
        }
        100% {
            max-height: 0;
            margin-bottom: -10px;
            padding-top: 0;
            padding-bottom: 0;
            opacity: 0;
        }
    }

    /* Cores das Bordas */
    .lunar-toast-success { border-left-color: #10b981; }
    .lunar-toast-error { border-left-color: #ef4444; }
    .lunar-toast-warning, .lunar-toast-warn { border-left-color: #f59e0b; }
    .lunar-toast-info { border-left-color: #3b82f6; }

    /* Cores dos Ícones */
    .text-success { color: #10b981; }
    .text-error { color: #ef4444; }
    .text-warning { color: #f59e0b; }
    .text-info { color: #3b82f6; }

    /* Elementos Internos */
    .lunar-toast-icon {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
    }
    .lunar-toast-content { flex-grow: 1; }
    .lunar-toast-title {
        margin: 0 0 4px 0;
        font-size: 14px;
        font-weight: 600;
        color: var(--color-text, #1f2937);
    }
    .lunar-toast-message {
        margin: 0;
        font-size: 13px;
        color: var(--color-text-muted, #4b5563);
        line-height: 1.4;
    }
    .lunar-toast-close {
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        color: var(--color-text-muted, #9ca3af);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .lunar-toast-close:hover { color: var(--color-text, #1f2937); }
</style>

<script>
    (function() {
        const initToastStore = () => {
            if (window.Alpine && !Alpine.store('toasts')) {
                Alpine.store('toasts', {
                    items: [],
                    position: 'top-right',

                    get positionStyle() {
                        switch(this.position) {
                            case 'top-left': return 'top: 0; left: 0; align-items: flex-start;';
                            case 'bottom-right': return 'bottom: 0; right: 0; align-items: flex-end; flex-direction: column-reverse;';
                            case 'bottom-left': return 'bottom: 0; left: 0; align-items: flex-start; flex-direction: column-reverse;';
                            case 'top-center': return 'top: 0; left: 50%; transform: translateX(-50%); align-items: center;';
                            case 'top-right':
                            default: return 'top: 0; right: 0; align-items: flex-end;';
                        }
                    },

                    add(message, type = 'info', title = '', duration = 4000) {
                        const id = Date.now() + Math.random();
                        const toast = { id, title, message, type, closing: false, duration, timer: null };

                        if (this.items.length >= 5) {
                            this.items.shift();
                        }

                        this.items.push(toast);
                        this.startTimer(toast);
                    },

                    startTimer(toast) {
                        toast.timer = setTimeout(() => {
                            this.remove(toast.id);
                        }, toast.duration);
                    },

                    pauseTimer(toast) {
                        clearTimeout(toast.timer);
                    },

                    resumeTimer(toast) {
                        this.startTimer(toast);
                    },

                    remove(id) {
                        const index = this.items.findIndex(t => t.id === id);
                        if (index > -1 && !this.items[index].closing) {
                            // Marca como fechando para disparar a animação de saída via classe .is-closing
                            this.items[index].closing = true;

                            // Remove do array de fato somente após o término da animação CSS (300ms)
                            // setTimeout(() => {
                            //     this.items = this.items.filter(t => t.id !== id);
                            // }, 300);
                        }
                    }
                });
            }
        };

        if (window.Alpine) { initToastStore(); }
        document.addEventListener('alpine:init', initToastStore);
    })();

    window.showToast = function(message, type = 'info', title = '') {
        if (window.Alpine && Alpine.store('toasts')) {
            Alpine.store('toasts').add(message, type, title);
        } else {
            console.error('Alpine Store de Toasts ainda não está pronta.');
        }
    };
</script>
