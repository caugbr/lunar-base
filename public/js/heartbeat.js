/**
 * Heartbeat Engine
 * Gerencia a comunicação periódica unificada com o backend.
 */
window.Heartbeat = (function () {
    let queue = {};
    let timer = null;
    let isPaused = false;
    const interval = 25000; // 25 segundos

    /**
     * Adiciona dados à próxima requisição do Heartbeat
     */
    function enqueue(handle, data) {
        queue[handle] = data;
    }

    /**
     * Pausa temporariamente o batimento (útil se o modal de login abrir)
     */
    function pause() {
        isPaused = true;
    }

    /**
     * Retoma os batimentos
     */
    function resume() {
        isPaused = false;
        beat(); // Dispara imediatamente
    }

    /**
     * Lê o valor de um cookie pelo nome
     */
    function getCookie(name) {
        let match = document.cookie.match(new RegExp('(^|;\\s*)(' + name + ')=([^;]*)'));
        return match ? decodeURIComponent(match[3]) : null;
    }

    /**
     * Executa o disparo do pulso
     */
    function beat() {
        if (isPaused) return;

        // Dispara evento para quem quiser anexar dados
        document.dispatchEvent(new CustomEvent('heartbeat-send', {
            detail: { enqueue }
        }));

        // Headers padrão
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        // 1. Tenta pegar da Meta Tag (se existir)
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag && metaTag.getAttribute('content')) {
            headers['X-CSRF-TOKEN'] = metaTag.getAttribute('content');
        } else {
            // 2. Se não existir meta tag, pega do Cookie oficial do Laravel!
            const xsrfToken = getCookie('XSRF-TOKEN');
            if (xsrfToken) {
                headers['X-XSRF-TOKEN'] = xsrfToken;
            }
        }

        const payload = { data: { ...queue } };
        queue = {};

        fetch('/admin/heartbeat', {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(payload)
        })
        .then(response => {
            // Sessão expirou (401) ou CSRF inválido (419)
            if (response.status === 401 || response.status === 419) {
                pause();

                // Emite evento específico de perda de autenticação
                document.dispatchEvent(new CustomEvent('heartbeat-auth-lost', {
                    detail: { status: response.status }
                }));

                throw new Error('Sessão expirada');
            }

            if (!response.ok) {
                throw new Error('Erro na resposta do Heartbeat');
            }

            return response.json();
        })
        .then(data => {
            // Dispara evento entregando a resposta do servidor para os módulos escutarem
            document.dispatchEvent(new CustomEvent('heartbeat-tick', {
                detail: data
            }));
        })
        .catch(error => {
            console.warn('[Heartbeat]', error.message);
        });
    }

    // Inicia o ciclo contínuo
    function start() {
        if (timer) clearInterval(timer);
        timer = setInterval(beat, interval);
        // Opcional: dispara um batimento 3 segundos após carregar a página
        setTimeout(beat, 3000);
    }

    // Inicialização automática ao carregar o DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }

    return {
        enqueue,
        pause,
        resume,
        beat
    };
})();
