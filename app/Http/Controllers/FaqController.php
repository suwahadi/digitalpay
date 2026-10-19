<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Helpers\ConfigHelper;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $category = $request->get('category');
        
        // Get site name for title
        $siteName = ConfigHelper::siteName();
        
        // Build FAQ query
        $faqQuery = Faq::active()->ordered();
        
        // Apply search filter
        if ($search) {
            $faqQuery->search($search);
        }
        
        // Apply category filter
        if ($category) {
            $faqQuery->category($category);
        }
        
        // Get FAQs
        $faqs = $faqQuery->get();
        
        // Get available categories
        $categories = Faq::getCategories();
        
        return Inertia::render('FAQ/Index', [
            'title' => 'FAQ - ' . $siteName,
            'faqs' => $faqs,
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'category' => $category,
            ],
        ]);
    }
}