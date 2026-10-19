<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Helpers\ConfigHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::active()->with(['category', 'variants' => function ($q) {
            $q->active()->ordered();
        }]);
        
        if ($request->has('search') && !empty($request->search)) {
            $query->search($request->search);
        }
        
        if ($request->has('category') && !empty($request->category)) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }
        
        $sortBy = $request->get('sort', 'newest');
        switch ($sortBy) {
            case 'price_low':
                $query->orderByRaw('(SELECT MIN(price) FROM product_variants WHERE product_id = products.id AND is_active = 1) ASC');
                break;
            case 'price_high':
                $query->orderByRaw('(SELECT MIN(price) FROM product_variants WHERE product_id = products.id AND is_active = 1) DESC');
                break;
            case 'popular':
                $query->popular()->latest();
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }
        
        $products = $query->paginate(36)->withQueryString();
        
        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'uuid' => $product->uuid,
                'name' => $product->name,
                'slug' => $product->slug,
                'image' => $product->image_url,
                'category' => $product->category->name,
                'min_price' => $product->min_price,
                'max_price' => $product->max_price,
                'stock_status' => $product->stock_status,
                'is_popular' => $product->is_popular,
                'url' => route('products.show', $product->slug),
            ];
        });
        
        $siteName = ConfigHelper::siteName();
        
        return Inertia::render('Products/Index', [
            'title' => 'Produk - ' . $siteName,
            'products' => $products,
            'categories' => $this->getCategories(),
            'filters' => [
                'search' => $request->search,
                'category' => $request->category,
                'sort' => $sortBy,
            ],
        ]);
    }
    
    public function byCategory(Category $category, Request $request)
    {
        $request->merge(['category' => $category->slug]);

        return $this->index($request);
    }
    
    public function show(Product $product)
    {
        if (!$product->is_active) {
            abort(404);
        }
        
        $product->load(['category', 'variants' => function ($q) {
            $q->active()->ordered();
        }]);
        
        $relatedProducts = Product::active()
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->inRandomOrder()
            ->limit(6)
            ->get()
            ->map(function ($relatedProduct) {
                return [
                    'id' => $relatedProduct->id,
                    'uuid' => $relatedProduct->uuid,
                    'name' => $relatedProduct->name,
                    'slug' => $relatedProduct->slug,
                    'image' => $relatedProduct->image_url,
                    'category' => $relatedProduct->category->name,
                    'min_price' => $relatedProduct->min_price,
                    'stock_status' => $relatedProduct->stock_status,
                    'url' => route('products.show', $relatedProduct->slug),
                ];
            });

        $siteName = ConfigHelper::siteName();

        $salesCount = \App\Models\OrderItem::where('product_id', $product->id)
            ->whereHas('order', function ($query) {
                $query->where('status', 'paid');
            })
            ->sum('quantity');

        return Inertia::render('Products/Show', [
            'title' => $product->name . ' - ' . $siteName,
            'product' => [
                'id' => $product->id,
                'uuid' => $product->uuid,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'short_description' => $product->short_description,
                'image' => $product->image_url,
                'features' => $product->features ?? [],
                'category' => [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                ],
                'variants' => $product->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'description' => $variant->description,
                        'price' => $variant->price,
                        'original_price' => $variant->original_price,
                        'discount_percentage' => $variant->discount_percentage,
                        'stock_status' => $variant->stock_status,
                        'duration_days' => $variant->duration_days,
                        'max_users' => $variant->max_users,
                        'is_active' => $variant->is_active,
                        'sort_order' => $variant->sort_order,
                        'formatted_price' => $variant->formatted_price,
                        'auto_delivery' => $variant->auto_delivery,
                        'formatted_original_price' => $variant->formatted_original_price,
                        'duration_text' => $variant->duration_text,
                    ];
                }),
                'min_price' => $product->min_price,
                'max_price' => $product->max_price,
                'stock_status' => $product->stock_status,
                'is_popular' => $product->is_popular,
                'is_featured' => $product->is_featured,
                'seo_title' => $product->seo_title,
                'seo_description' => $product->seo_description,
                'sales_count' => $salesCount,
            ],
            'relatedProducts' => $relatedProducts,
            'auth' => [
                'user' => Auth::user() ? [
                    'id' => Auth::user()->id,
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'role' => Auth::user()->role,
                ] : null
            ],
        ]);
    }
    
    public function popular()
    {
        try {
            $products = Product::active()
                ->popular()
                ->with(['category', 'variants' => function ($q) {
                    $q->active()->ordered();
                }])
                ->limit(8)
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
                        'url' => route('products.show', $product->slug),
                    ];
                });
                
            return response()->json($products);
        } catch (\Exception $e) {
            report($e);
            return response()->json([]);
        }
    }
    
    public function featured()
    {
        try {
            $products = Product::active()
                ->featured()
                ->with(['category', 'variants' => function ($q) {
                    $q->active()->ordered();
                }])
                ->limit(8)
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
                        'url' => route('products.show', $product->slug),
                    ];
                });
                
            return response()->json($products);
        } catch (\Exception $e) {
            report($e);
            return response()->json([]);
        }
    }
    
    public function groupedByCategory()
    {
        try {
            $categories = Category::active()
                ->with(['products' => function ($query) {
                    $query->active()
                        ->popular()
                        ->with(['variants' => function ($q) {
                            $q->active()->ordered();
                        }])
                        ->limit(6);
                }])
                ->ordered()
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'icon' => $category->icon ?? 'lucide:package',
                        'products' => $category->products->map(function ($product) {
                            return [
                                'id' => $product->id,
                                'uuid' => $product->uuid,
                                'name' => $product->name,
                                'slug' => $product->slug,
                                'image' => $product->image_url,
                                'min_price' => $product->min_price,
                                'stock_status' => $product->stock_status,
                                'url' => route('products.show', $product->slug),
                            ];
                        }),
                    ];
                });
                
            return response()->json($categories);
        } catch (\Exception $e) {
            report($e);
            return response()->json([]);
        }
    }
    
    private function getCategories()
    {
        try {
            return Category::active()
                ->ordered()
                ->get(['id', 'name', 'slug'])
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ];
                });
        } catch (\Exception $e) {
            report($e);
            return [];
        }
    }
    
    public function search(Request $request)
    {
        try {
            $query = $request->get('q', '');
            
            if (empty($query) || strlen($query) < 2) {
                return response()->json(['products' => []]);
            }
            
            $products = Product::active()
                ->search($query)
                ->with(['category', 'variants' => function ($q) {
                    $q->active()->ordered();
                }])
                ->limit(10)
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
                        'price' => $product->min_price,
                        'stock_status' => $product->stock_status,
                        'url' => route('products.show', $product->slug),
                    ];
                });
                
            return response()->json(['products' => $products]);
        } catch (\Exception $e) {
            report($e);
            return response()->json(['products' => []], 500);
        }
    }
}