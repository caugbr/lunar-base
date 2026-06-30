<?php

namespace Plugins\Comments;

use Illuminate\Support\ServiceProvider;
use App\Models\Post;
use Plugins\Comments\Models\Comment;

class CommentsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Inject the polymorphic relationship on the fly into the Post model
        Post::resolveRelationUsing('comments', function ($post) {
            return $post->morphMany(Comment::class, 'commentable')
                ->whereNull('parent_id')
                ->where('status', 'approved')
                ->orderBy('created_at', 'desc');
        });

        // Load plugin resources
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'comments');
    }
}
