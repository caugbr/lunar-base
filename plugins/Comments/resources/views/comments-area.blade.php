<div class="comments-area">
    <h3 class="comments-title">
        Comments ({{ $model->comments()->count() }})
    </h3>

    @if(session('comment_status'))
        <div class="comments-alert comments-alert-success">
            {{ session('comment_status') }}
        </div>
    @endif

    <!-- Comments List -->
    <div class="comments-list">
        @forelse($model->comments as $comment)
            @include('comments::partials.comment-item', ['comment' => $comment])
        @empty
            <p class="comments-empty-text">No comments yet. Be the first to write one!</p>
        @endforelse
    </div>

    <!-- Main Comment Form -->
    <form action="{{ route('comments.store') }}" method="POST" class="comment-form">
        @csrf
        <input type="hidden" name="commentable_id" value="{{ $model->id }}">
        <input type="hidden" name="commentable_type" value="{{ get_class($model) }}">
        <input type="hidden" id="form-parent-id" name="parent_id" value="">

        <!-- Active Reply Alert -->
        <div id="reply-alert" class="reply-alert" style="display: none;">
            <span class="reply-alert-text">
                Replying to <strong id="reply-author"></strong>
            </span>
            <button type="button" onclick="cancelReply()" class="reply-alert-close btn" aria-label="Cancel reply">
                <x-lucide-x class="lucid-icon" />
            </button>
        </div>

        @guest
            <div class="comment-form-row">
                <div class="comment-form-group">
                    <label for="author_name" class="comment-form-label">Name</label>
                    <input type="text" name="author_name" id="author_name" required class="form-input" value="{{ old('author_name') }}">
                    @error('author_name') <span class="comment-error">{{ $message }}</span> @enderror
                </div>
                <div class="comment-form-group">
                    <label for="author_email" class="comment-form-label">Email</label>
                    <input type="email" name="author_email" id="author_email" required class="form-input" value="{{ old('author_email') }}">
                    @error('author_email') <span class="comment-error">{{ $message }}</span> @enderror
                </div>
            </div>
        @endguest

        <div class="comment-form-group">
            <label for="comment-content" class="comment-form-label">Your Comment</label>
            <textarea name="content" id="comment-content" rows="4" required class="form-input">{{ old('content') }}</textarea>
            @error('content') <span class="comment-error">{{ $message }}</span> @enderror
        </div>

        <!-- Alinhado à direita com espaçamento superior -->
        <div class="buttons">
            <button type="submit" class="btn btn-primary">
                Post Comment
            </button>
        </div>
    </form>
</div>

@once
@push('styles')
<style>
    /* --- CSS Temático da Área de Comentários (Lunar Base) --- */

    .comments-area {
        margin-top: var(--space-md, 2rem);
        margin-bottom: var(--space-sm, 1rem);
        background-color: var(--color-bg-card, #1A1E3A);
        padding: var(--space-sm, 1rem) var(--space-md, 2rem);
        border-radius: 12px;
        border: 1px solid var(--color-border, rgba(200, 182, 255, 0.12));
        font-family: var(--font-body, 'Inter', sans-serif);
        color: var(--color-text, #E8E6F0);
    }

    .comments-title {
        font-family: var(--font-heading, 'Cormorant Garamond', serif);
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--color-text, #E8E6F0);
        margin-bottom: var(--space-sm, 1rem);
    }

    .comments-alert-success {
        margin-bottom: var(--space-sm, 1rem);
        padding: var(--space-sm, 1rem);
        font-size: 0.875rem;
        color: var(--color-text, #E8E6F0);
        background-color: var(--color-featured-bg, #201160);
        border-radius: 6px;
        border: 1px solid var(--color-border, rgba(200, 182, 255, 0.12));
    }

    .comments-empty-text {
        color: var(--color-text-muted, #8A87A8);
        font-size: 0.875rem;
    }

    /* Listagem de comentários */
    .comments-list {
        margin-bottom: var(--space-md, 2rem);
    }

    .comments-list > * + * {
        margin-top: var(--space-sm, 1rem);
    }

    /* Caixa do Comentário */
    .comment-item {
        display: flex;
        gap: var(--space-sm, 1rem);
        padding: var(--space-sm, 1rem);
        border-radius: 8px;
        background-color: var(--color-bg-dark, #12152B);
        border: 1px solid var(--color-border, rgba(200, 182, 255, 0.12));
    }

    .comment-avatar {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        background-color: var(--color-bg-card, #1A1E3A);
        color: var(--color-text-muted, #8A87A8);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
        font-size: 0.875rem;
        text-transform: uppercase;
        border: 1px solid var(--color-border, rgba(200, 182, 255, 0.12));
    }

    .comment-body {
        flex-grow: 1;
        min-width: 0;
    }

    .comment-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.25rem;
    }

    .comment-author {
        font-weight: 700;
        color: var(--color-text, #E8E6F0);
        font-size: 0.875rem;
    }

    .comment-date {
        font-size: 0.75rem;
        color: var(--color-text-muted, #8A87A8);
    }

    .comment-text {
        color: var(--color-text, #E8E6F0);
        font-size: 0.875rem;
        line-height: 1.5;
        margin-bottom: var(--space-xs, 0.5rem);
        word-wrap: break-word;
    }

    .comment-reply-btn {
        background: none;
        border: none;
        padding: 0;
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--color-primary-dark, #9D7CFF);
        cursor: pointer;
        transition: color var(--transition-fast, 0.2s ease);
    }
    .comment-reply-btn:hover {
        color: var(--color-primary, #C8B6FF);
        text-decoration: underline;
    }

    /* Respostas Aninhadas */
    .comment-replies {
        margin-top: var(--space-sm, 1rem);
        border-left: 2px solid var(--color-border, rgba(200, 182, 255, 0.12));
        padding-left: var(--space-sm, 1rem);
    }
    .comment-replies > * + * {
        margin-top: var(--space-sm, 1rem);
    }

    /* Formulário e Estilização Unificada do TextArea */
    .comment-form {
        border-top: 1px solid var(--color-border, rgba(200, 182, 255, 0.12));
        padding-top: var(--space-sm, 1rem);
    }

    .comment-form-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: var(--space-sm, 1rem);
        margin-bottom: var(--space-sm, 1rem);
    }
    @media (min-width: 640px) {
        .comment-form-row {
            grid-template-columns: 1fr 1fr;
        }
    }

    .comment-form-group {
        display: flex;
        flex-direction: column;
        gap: var(--space-xs, 0.5rem);
        margin-bottom: var(--space-sm, 1rem);
    }

    .comment-form-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--form-label-color, #fafafa);
    }

    /* Estilização completa e unificada da Textarea e Inputs usando suas variáveis */
    .comment-form .form-input {
        width: 100%;
        background-color: var(--form-input-bg, #4b5563);
        border: 1px solid var(--form-input-border-color, #e9e9e9);
        color: var(--form-input-color, #ffffff);
        padding: var(--space-xs, 0.5rem) var(--space-sm, 1rem);
        border-radius: 6px;
        font-family: var(--font-body, sans-serif);
        font-size: 0.875rem;
        box-sizing: border-box;
        transition: border-color var(--transition-fast, 0.2s ease), box-shadow var(--transition-fast, 0.2s ease);
    }
    .comment-form .form-input:focus {
        outline: none;
        border-color: var(--color-primary, #C8B6FF);
        box-shadow: 0 0 0 2px var(--color-glow, rgba(157, 124, 255, 0.15));
    }
    .comment-form textarea.form-input {
        resize: vertical;
        min-height: 110px;
    }

    .comment-error {
        font-size: 0.75rem;
        color: #dc2626;
        margin-top: 0.25rem;
    }

    /* Container do botão de envio */
    .comment-form .buttons {
        display: flex;
        justify-content: flex-end;
        margin-top: var(--space-sm, 1rem);
    }

    .comment-form .admin-btn-primary {
        background-color: var(--form-button-bg, #333333);
        color: var(--form-button-color, #ffffff);
        border-radius: 9999px;
        padding: 0.5rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: background-color var(--transition-fast, 0.2s ease), transform var(--transition-fast, 0.2s ease);
    }
    .comment-form .admin-btn-primary:hover {
        background-color: var(--color-primary-dark, #9D7CFF);
    }

    /* Alerta de Resposta ativa */
    .reply-alert {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: var(--color-glow, rgba(157, 124, 255, 0.15));
        border: 1px solid var(--color-border, rgba(200, 182, 255, 0.12));
        padding: 0.75rem;
        border-radius: 0.5rem;
        margin-bottom: var(--space-sm, 1rem);
    }

    .reply-alert-text {
        font-size: 0.875rem;
        color: var(--color-primary, #C8B6FF);
    }

    .reply-alert-close {
        background: transparent;
        border: none;
        cursor: pointer;
        color: var(--color-text-muted, #8A87A8);
        display: inline-flex;
        align-items: center;
    }
    .reply-alert-close .lucid-icon {
        width: 14px;
        height: 14px;
    }
</style>
@endpush
@endonce

<script>
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
</script>
