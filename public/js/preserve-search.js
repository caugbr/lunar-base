
// Preservar parametros de busca ao navegar
(function() {
    const path = window.location.pathname;
    const key  = `filters_${path.replace(/\//g, '_')}`;

    // Lê params atuais e filtra em um NOVO URLSearchParams
    const rawParams = new URLSearchParams(window.location.search);
    const params = new URLSearchParams();

    for (let [k, v] of rawParams) {
        if (v && k !== 'page') {
            params.append(k, v);
        }
    }

    if (rawParams.toString() && !params.toString()) {
        localStorage.removeItem(key);
        return;
    }

    if (params.toString()) {
        localStorage.setItem(key, params.toString());
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('.admin-filters');
            if (form) {
                form.classList.add('searched');
            }
        });
    } else if (localStorage.getItem(key)) {
        window.location.search = localStorage.getItem(key);
        return;
    }

    document.addEventListener('click', function(e) {
        if (e.target.closest('.admin-filter-actions a')) {
            localStorage.removeItem(key);
        }
    });
})();
