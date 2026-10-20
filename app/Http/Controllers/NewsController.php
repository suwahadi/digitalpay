<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Helpers\ConfigHelper;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::published()->with('author');
        
        if ($request->has('search') && !empty($request->search)) {
            $query->search($request->search);
        }
        
        if ($request->has('category') && !empty($request->category)) {
            $query->category($request->category);
        }
        
        $articles = $query->latest('published_at')
            ->paginate(12)
            ->withQueryString();
            
        $articles->getCollection()->transform(function ($article) {
            return [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'featured_image' => $article->featured_image_url,
                'published_date' => $article->published_at->translatedFormat('d M Y'),
                'author' => $article->author->name,
                'category' => $article->category,
                'reading_time' => $article->reading_time,
                'url' => route('news.show', $article->slug),
            ];
        });
        
        $siteName = ConfigHelper::siteName();
        
        return Inertia::render('News/Index', [
            'title' => 'Berita - ' . $siteName,
            'articles' => $articles,
            'filters' => [
                'search' => $request->search,
                'category' => $request->category,
            ],
            'categories' => $this->getCategories(),
        ]);
    }
    
    public function show(Article $article)
    {
        if (!$article->is_published || $article->published_at > now()) {
            abort(404);
        }
        
        $article->load('author');
        
        $relatedArticles = Article::published()
            ->where('id', '!=', $article->id)
            ->where('category', $article->category)
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->map(function ($relatedArticle) {
                return [
                    'id' => $relatedArticle->id,
                    'title' => $relatedArticle->title,
                    'slug' => $relatedArticle->slug,
                    'excerpt' => $relatedArticle->excerpt,
                    'featured_image' => $relatedArticle->featured_image_url,
                    'published_date' => $relatedArticle->published_at->translatedFormat('d M Y'),
                    'url' => route('news.show', $relatedArticle->slug),
                ];
            });
        
        $siteName = ConfigHelper::siteName();
        
        return Inertia::render('News/Show', [
            'title' => $article->title . ' - ' . $siteName,
            'article' => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'content' => $article->content,
                'featured_image' => $article->featured_image_url,
                'published_date' => $article->published_at->translatedFormat('d M Y'),
                'author' => $article->author->name,
                'category' => $article->category,
                'reading_time' => $article->reading_time,
                'seo_title' => $article->seo_title,
            ],
            'relatedArticles' => $relatedArticles,
        ]);
    }
    
    private function getCategories()
    {
        try {
            return Article::published()
                ->select('category')
                ->distinct()
                ->whereNotNull('category')
                ->pluck('category')
                ->filter()
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}