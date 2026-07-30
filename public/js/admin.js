document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.admin-sidebar');

    // Menu mobile
    const toggleBtn = document.getElementById('menuToggle');

    // Cria o overlay
    const overlay = document.createElement('div');
    overlay.className = 'admin-overlay';
    document.body.appendChild(overlay);

    function openMenu() {
        sidebar.classList.add('open');
        toggleBtn.classList.add('active');
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        sidebar.classList.remove('open');
        toggleBtn.classList.remove('active');
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    toggleBtn.addEventListener('click', function() {
        if (sidebar.classList.contains('open')) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    overlay.addEventListener('click', closeMenu);

    // Fechar ao redimensionar para desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeMenu();
        }
    });

    // Menu desktop
    sidebar.addEventListener('click', event => {
        const toggleItem = event.target.closest('span.dropdown-arrow');
        if (toggleItem) {
            event.preventDefault();
            const parent = toggleItem.closest('.admin-nav-dropdown');
            parent.classList.toggle('open');
        }
    });
});

function debounce(func, delay) {
    let timeoutId;

    return function(...args) {
        clearTimeout(timeoutId);

        timeoutId = setTimeout(() => {
            func.apply(this, args);
        }, delay);
    };
}
