<div class="comment-item">
    <!-- User Avatar / Initials -->
    <div class="comment-avatar">
        {{ substr($comment->author_name, 0, 2) }}
    </div>

    <div class="comment-body">
        <div class="comment-header">
            <span class="comment-author">
                {{ $comment->author_name }}
            </span>
            <span class="comment-date">
                {{ $comment->created_at->diffForHumans() }}
            </span>
        </div>

        <p class="comment-text">
            {{ $comment->content }}
        </p>

        <!-- Reply Button -->
        <button type="button" onclick="setReply({{ $comment->id }}, '{{ $comment->author_name }}')" class="btn comment-reply-btn">
            Responder
        </button>

        <!-- Recursive Nested Replies -->
        @if($comment->replies->count() > 0)
            <div class="comment-replies">
                @foreach($comment->replies as $reply)
                    @include('comments::partials.comment-item', ['comment' => $reply])
                @endforeach
            </div>
        @endif
    </div>
</div>
