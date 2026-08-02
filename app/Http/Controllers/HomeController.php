<?php

namespace App\Http\Controllers;

use App\Models\Post;

class HomeController extends Controller
{

    public function index()
    {
        $amount = setting('reading.max_featured_posts', 5);
        $featuredPosts = Post::published()->featured()->latest('published_at')->take($amount)->get();
        return view('public.home', compact('featuredPosts'));
    }
}
