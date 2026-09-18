/**
 * Content Lock Handler
 * Gerencia a comunicação do lock com o Heartbeat e a UI de aviso/bloqueio.
 */
document.addEventListener('DOMContentLoaded', function () {
    // Procura na página os dados do conteúdo sendo editado
    // Pode vir de meta tags ou de inputs escondidos
    const lockModal = document.getElementById('content-lock-modal');

    if (!lockModal) {
        return; // Não está numa tela com lock
    }

    const contentType = lockModal.getAttribute('data-lock-type');
    const contentId = lockModal.getAttribute('data-lock-id');

    if (!contentType || !contentId) {
        return;
    }

    let isLockedByOther = false;

    // ANTES DE CADA PULSO: Diz pro Heartbeat incluir nosso lock
    document.addEventListener('heartbeat-send', function (e) {
        e.detail.enqueue('content_lock', {
            type: contentType,
            id: contentId
        });
    });

    // NA RESPOSTA DO PULSO: Analisa o status do lock
    document.addEventListener('heartbeat-tick', function (e) {
        const response = e.detail.content_lock;
        if (!response) return;

        if (response.status === 'locked') {
            // Outro usuário está com o lock (ou tomou o controle de nós!)
            handleLockedUI(response.locked_by);
        } else if (response.status === 'active') {
            // Somos o dono legítimo do lock
            handleActiveUI();
        }
    });

    // Manipula a interface quando o conteúdo está bloqueado
    function handleLockedUI(lockedBy) {
        if (isLockedByOther) return; // Já está exibindo
        isLockedByOther = true;

        // Atualiza nome de quem está editando no modal
        const userPlaceholder = document.getElementById('lock-user-name');
        if (userPlaceholder) {
            userPlaceholder.textContent = lockedBy || 'Outro usuário';
        }

        // Abre o modal de bloqueio
        const modal = document.getElementById('content-lock-modal');
        if (modal) {
            modal.style.display = 'flex';
        }

        // Opcional: Desativa inputs e botões de salvar para proteger o formulário
        disableForm();
    }

    function handleActiveUI() {
        if (!isLockedByOther) return;
        isLockedByOther = false;

        const modal = document.getElementById('content-lock-modal');
        if (modal) {
            modal.style.display = 'none';
        }

        enableForm();
    }

    function disableForm() {
        document.querySelectorAll('form button[type="submit"], form input, form textarea, form select').forEach(el => {
            el.disabled = true;
        });
    }

    function enableForm() {
        document.querySelectorAll('form button[type="submit"], form input, form textarea, form select').forEach(el => {
            el.disabled = false;
        });
    }

    // AÇÃO DE TAKE OVER (Assumir o controle)
    const btnTakeover = document.getElementById('btn-lock-takeover');
    if (btnTakeover) {
        btnTakeover.addEventListener('click', function () {
            btnTakeover.disabled = true;
            btnTakeover.textContent = 'Assumindo...';

            fetch('/admin/content-lock/takeover', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ type: contentType, id: contentId })
            })
            .then(res => res.json())
            .then(() => {
                // Força um pulso imediato do Heartbeat para renovar o estado
                handleActiveUI();
                if (window.Heartbeat) {
                    window.Heartbeat.beat();
                }
            })
            .finally(() => {
                btnTakeover.disabled = false;
                btnTakeover.textContent = 'Assumir o controle';
            });
        });
    }

    // LIBERAR LOCK AO FECHAR OU NAVEGAR
    window.addEventListener('beforeunload', function () {
        if (!isLockedByOther) {
            navigator.sendBeacon('/admin/content-lock/release', new Blob([JSON.stringify({
                type: contentType,
                id: contentId,
                _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            })], { type: 'application/json' }));
        }
    });
});
