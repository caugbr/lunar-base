<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Helpers\ContentHelper;

class PublicPageController extends Controller
{
    public function show($slug)
    {
        $page = Page::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $page->content = ContentHelper::parseShortcodes($page->content);

        $templateName = $page->template ?? config('pageTemplates.default');
        $template = 'public.templates.' . $templateName;

        return view($template, compact('page'));
    }
}
