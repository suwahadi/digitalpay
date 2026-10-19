<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Article;
use App\Models\PaymentMethod;
use App\Models\Faq;
use App\Models\Setting;
use App\Models\Slide;
use App\Helpers\ConfigHelper;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $siteName = ConfigHelper::siteName();
        $siteTagline = ConfigHelper::siteTagline();

        return Inertia::render('Home/Index', [
            'title' => $siteName . ' - ' . $siteTagline,
            'sitetagline' => $siteTagline,
            'hero' => $this->getHeroData(),
            'popularProducts' => $this->getPopularProducts(),
            'recentNews' => $this->getRecentNews(),
            'faqs' => $this->getFaqs(),
            'paymentMethods' => $this->getPaymentMethods(),
        ]);
    }

    protected function getHeroData()
    {
        try {
            $slides = Slide::active()
                ->ordered()
                ->get()
                ->map(function ($slide) {
                    return [
                        'id' => $slide->id,
                        'image' => $slide->image_url,
                        'title' => $slide->title,
                        'link' => $slide->link ?? '#',
                    ];
                });

            return [
                'slides' => $slides
            ];
        } catch (\Exception $e) {
            return [
                'slides' => [
                    [
                        'id' => 1,
                        'image' => '/images/placeholder.png',
                        'title' => 'Banner',
                        'link' => '#',
                    ],
                    [
                        'id' => 2,
                        'image' => '/images/placeholder.png',
                        'title' => 'Banner',
                        'link' => '#',
                    ],
                    [
                        'id' => 3,
                        'image' => '/images/placeholder.png',
                        'title' => 'Banner',
                        'link' => '#',
                    ],
                ]
            ];
        }
    }

    protected function getWorkHours()
    {
        $currentDay = strtolower(now()->format('l'));
        $workHours = [
            'monday' => ['start' => '09:00', 'end' => '21:00', 'is_holiday' => false],
            'tuesday' => ['start' => '09:00', 'end' => '21:00', 'is_holiday' => false],
            'wednesday' => ['start' => '09:00', 'end' => '21:00', 'is_holiday' => false],
            'thursday' => ['start' => '09:00', 'end' => '21:00', 'is_holiday' => false],
            'friday' => ['start' => '13:00', 'end' => '21:00', 'is_holiday' => true],
            'saturday' => ['start' => '09:00', 'end' => '21:00', 'is_holiday' => true],
            'sunday' => ['start' => '09:00', 'end' => '21:00', 'is_holiday' => true],
        ];

        $today = $workHours[$currentDay] ?? null;
        $currentTime = now()->format('H:i');
        
        $isOpen = false;
        if ($today) {
            $isOpen = $currentTime >= $today['start'] && $currentTime <= $today['end'];
        }

        return [
            'current_status' => [
                'is_open' => $isOpen,
                'hours' => $today ? $today['start'] . ' - ' . $today['end'] . ' WIB' : 'Tutup',
                'day' => ucfirst($currentDay),
            ],
            'schedule' => $workHours,
        ];
    }

    protected function getPopularProducts()
    {
        try {
            return Product::with(['category', 'variants' => function ($query) {
                $query->active()->ordered();
            }])
                ->active()
                ->popular()
                ->limit(9)
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'uuid' => $product->uuid,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'image' => $product->image_url,
                        'category' => $product->category->name,
                        'min_price' => $product->min_price,
                        'stock_status' => $product->stock_status,
                        'is_popular' => $product->is_popular,
                    ];
                });
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getProductsByCategory()
    {
        try {
            return Category::with(['products' => function ($query) {
                $query->active()
                    ->with(['variants' => function ($q) {
                        $q->active()->ordered();
                    }])
                    ->limit(12);
            }])
                ->active()
                ->ordered()
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'icon' => $category->icon ?? 'lucide:package',
                        'products_count' => $category->products_count ?? $category->products->count(),
                        'products' => $category->products->map(function ($product) {
                            return [
                                'id' => $product->id,
                                'uuid' => $product->uuid,
                                'name' => $product->name,
                                'slug' => $product->slug,
                                'image' => $product->image_url,
                                'min_price' => $product->min_price,
                                'stock_status' => $product->stock_status,
                            ];
                        }),
                    ];
                });
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getRecentNews()
    {
        try {
            return Article::published()
                ->with('author')
                ->latest('published_at')
                ->limit(3)
                ->get()
                ->map(function ($article) {
                    return [
                        'id' => $article->id,
                        'title' => $article->title,
                        'slug' => $article->slug,
                        'excerpt' => $article->excerpt,
                        'featured_image' => $article->featured_image_url,
                        'published_date' => $article->published_at->format('d M Y'),
                        'author' => $article->author->name,
                        'url' => route('news.show', $article->slug),
                    ];
                });
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getFaqs()
    {
        try {
            return Faq::active()
                ->ordered()
                ->limit(10)
                ->get(['id', 'question', 'answer']);
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getPaymentMethods()
    {
        try {
            return PaymentMethod::where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'icon', 'type'])
                ->map(function ($method) {
                    return [
                        'id' => $method->id,
                        'name' => $method->name,
                        'logo' => $method->logo_url,
                        'type' => $method->type,
                    ];
                });
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function getSettings()
    {
        try {
            return ConfigHelper::globalSettings();
        } catch (\Exception $e) {
            return [
                'site_name' => ConfigHelper::siteName(),
                'site_tagline' => ConfigHelper::siteTagline(),
                'title' => ConfigHelper::siteName(),
                'tagline' => ConfigHelper::siteTagline(),
                'description' => ConfigHelper::siteDescription(),
                'logo' => ConfigHelper::siteLogo(),
                'favicon' => ConfigHelper::siteFavicon(),
                'contact' => ConfigHelper::contactSettings(),
                'social' => ConfigHelper::socialSettings(),
            ];
        }
    }

    public function paymentMethods()
    {
        return response()->json($this->getPaymentMethods());
    }

    public function workHours()
    {
        return response()->json($this->getWorkHours());
    }
}
