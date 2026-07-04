<div class="comments-area">
    <h3 class="comments-title">
        Comentários ({{ $model->comments()->count() }})
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
            <p class="comments-empty-text">Sem comentários ainda. Seja o primeiro a comentar!</p>
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
                Respondendo a <strong id="reply-author"></strong>
            </span>
            <button type="button" onclick="cancelReply()" class="reply-alert-close btn" aria-label="Cancel reply">
                <x-lucide-x class="lucid-icon" />
            </button>
        </div>

        @guest
            <div class="comment-form-row">
                <div class="comment-form-group">
                    <label for="author_name" class="comment-form-label">Nome</label>
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
            <label for="comment-content" class="comment-form-label">Seu comentário</label>
            <textarea name="content" id="comment-content" rows="4" required class="form-input">{{ old('content') }}</textarea>
            @error('content') <span class="comment-error">{{ $message }}</span> @enderror
        </div>

        <!-- Alinhado à direita com espaçamento superior -->
        <div class="buttons">
            <button type="submit" class="btn btn-primary">
                Comentar
            </button>
        </div>
    </form>
</div>

@once
@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/comments/css/comments.css') }}">
@endpush
@endonce

<script src="{{ asset('plugins/comments/js/comments.js') }}"></script>
