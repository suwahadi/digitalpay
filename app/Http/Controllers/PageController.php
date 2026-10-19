<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Helpers\ConfigHelper;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PageController extends Controller
{
    public function show(Page $page)
    {
        if (!$page->is_published) {
            abort(404);
        }
        
        // Get site name for title
        $siteName = ConfigHelper::siteName();
        
        return Inertia::render('Page/Show', [
            'title' => $page->title . ' - ' . $siteName,
            'page' => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'content' => $page->content,
                'created_at' => $page->created_at,
                'updated_at' => $page->updated_at,
            ],
        ]);
    }
}