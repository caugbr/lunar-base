<?php

namespace Plugins\Comments\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Plugins\Comments\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Store a newly created comment in storage.
     */
    public function store(Request $request)
    {
        // 1. Define as regras básicas para qualquer comentário
        $rules = [
            'commentable_id'   => 'required|integer',
            'commentable_type' => 'required|string',
            'parent_id'        => 'nullable|exists:comments,id',
            'content'          => 'required|string|min:3|max:1000',
        ];

        // 2. Se for um visitante (não logado), exige nome e email no formulário
        if (!Auth::check()) {
            $rules['author_name'] = 'required|string|max:255';
            $rules['author_email'] = 'required|email|max:255';
        }

        // 3. Executa a validação segura
        $validated = $request->validate($rules);

        $userId = Auth::id();
        $authorName = $userId ? Auth::user()->name : $validated['author_name'];
        $authorEmail = $userId ? Auth::user()->email : $validated['author_email'];

        // 4. Define o status inicial
        $status = 'approved';

        // Moderação obrigatória: tudo entra como pendente
        if (setting('comments.comments_require_moderation', false)) {
            $status = 'pending';
        } else {
            // Spam check como fallback quando moderação está desativada
            $spamKeywords = ['buy', 'viagra', 'free casino', 'spam', 'cryptocurrency', 'click here'];

            foreach ($spamKeywords as $keyword) {
                if (stripos($validated['content'], $keyword) !== false) {
                    $status = 'pending';
                    break;
                }
            }
        }

        Comment::create([
            'commentable_id'   => $validated['commentable_id'],
            'commentable_type' => $validated['commentable_type'],
            'parent_id'        => $validated['parent_id'],
            'user_id'          => $userId,
            'author_name'      => $authorName,
            'author_email'     => $authorEmail,
            'content'          => strip_tags($validated['content']),
            'status'           => $status
        ]);

        $statusMessage = $status === 'approved'
            ? 'Your comment has been posted successfully!'
            : 'Your comment has been received and is awaiting moderation.';

        return back()->with('comment_status', $statusMessage);
    }
}