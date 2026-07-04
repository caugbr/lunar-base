<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Page;
use App\Models\Post;
// use App\Models\Form;
use App\Models\Media;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users'   => User::count(),
            'pages'   => Page::count(),
            'posts'   => Post::count(),
            // 'forms'   => Form::count(),
            'media'   => Media::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
