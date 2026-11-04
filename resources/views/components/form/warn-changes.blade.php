<script>
(() => {
    // Captura imediatamente o formulário pai no momento em que a tag é lida
    const form = document.currentScript ? document.currentScript.closest('form') : null;
    if (!form) return;

    let isDirty = false;

    form.addEventListener('input', () => isDirty = true);
    form.addEventListener('change', () => isDirty = true);
    form.addEventListener('reset', () => isDirty = false);

    form.addEventListener('submit', (e) => {
        if (!e.defaultPrevented && form.checkValidity()) {
            isDirty = false;
        }
    });

    window.addEventListener('editor:dirty', () => isDirty = true);

    window.addEventListener('beforeunload', (e) => {
        if (isDirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
})();
</script>
