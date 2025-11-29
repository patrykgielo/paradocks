<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PostController extends Controller
{
    public function show(string $slug)
    {
        $post = Post::where('slug', $slug)
            ->where('published_at', '<=', now())
            ->with('category')
            ->firstOrFail();

        return view('posts.show', compact('post'));
    }
}
