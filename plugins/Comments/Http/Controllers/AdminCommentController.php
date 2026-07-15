<?php

namespace Plugins\Comments\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Plugins\Comments\Models\Comment;

class AdminCommentController extends Controller
{
    /**
     * Display a listing of comments for moderation.
     */
    public function index(Request $request)
    {
        $query = Comment::with(['commentable', 'user'])
            ->orderBy('created_at', 'desc');

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por busca (autor ou conteúdo)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('author_name', 'like', "%{$search}%")
                  ->orWhere('author_email', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $comments = $query->paginate(setting('comments.pagination_items', 20));
        $statuses = ['approved' => 'Aprovado', 'pending' => 'Pendente', 'spam' => 'Spam', 'rejected' => 'Rejeitado'];

        return view('comments::admin.index', compact('comments', 'statuses'));
    }

    /**
     * Update the status of a comment (approve or reject).
     */
    public function update(Request $request, Comment $comment)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $comment->update(['status' => $request->status]);

        $labels = [
            'approved' => 'aprovado',
            'rejected' => 'rejeitado',
        ];

        return back()->with('success', "Comentário {$labels[$request->status]} com sucesso.");
    }

    /**
     * Bulk update status for multiple comments (approve or reject only).
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:comments,id',
            'status' => 'required|in:approved,rejected',
        ]);

        Comment::whereIn('id', $request->ids)->update(['status' => $request->status]);

        $labels = [
            'approved' => 'aprovados',
            'rejected' => 'rejeitados',
        ];

        return back()->with('success', count($request->ids) . " comentários {$labels[$request->status]} com sucesso.");
    }

    /**
     * Bulk delete multiple comments (used for spam and normal deletion).
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:comments,id',
        ]);

        Comment::whereIn('id', $request->ids)->delete();

        return back()->with('success', count($request->ids) . ' comentário(s) excluído(s) com sucesso.');
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();

        return back()->with('success', 'Comentário excluído com sucesso.');
    }
}
