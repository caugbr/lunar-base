function setReply(parentId, authorName) {
    document.getElementById('form-parent-id').value = parentId;
    document.getElementById('reply-author').innerText = authorName;
    document.getElementById('reply-alert').style.display = 'flex';

    // Scroll suave para o formulário
    document.getElementById('comment-content').focus();
    document.getElementById('comment-content').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function cancelReply() {
    document.getElementById('form-parent-id').value = '';
    document.getElementById('reply-alert').style.display = 'none';
}
