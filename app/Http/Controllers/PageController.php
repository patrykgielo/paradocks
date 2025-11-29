<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::where('slug', $slug)
            ->where('published_at', '<=', now())
            ->firstOrFail();

        return view('pages.show', compact('page'));
    }
}
