@props(["selector" => "form"])

<script>
(() => {
    // Tudo aqui dentro agora roda em um escopo isolado e seguro
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('{{ $selector }}');
        if (!form) return;

        let isDirty = false;

        form.addEventListener('input', () => isDirty = true);
        form.addEventListener('change', () => isDirty = true);

        if (typeof tinymce !== 'undefined') {
            tinymce.on('AddEditor', (e) => {
                e.editor.on('change', () => isDirty = true);
                e.editor.on('input', () => isDirty = true);
            });
        }

        form.addEventListener('submit', () => {
            isDirty = false;
        });

        window.addEventListener('beforeunload', (e) => {
            const tinyMceDirty = typeof tinymce !== 'undefined' && tinymce.activeEditor && tinymce.activeEditor.isDirty();

            if (isDirty || tinyMceDirty) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    });
})();
</script>
