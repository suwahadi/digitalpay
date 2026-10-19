<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Helpers\ConfigHelper;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
            ],
            // Provide categories globally for navigation
            'categories' => function () {
                try {
                    return \App\Models\Category::active()
                        ->ordered()
                        ->limit(10)
                        ->get(['id', 'name', 'slug', 'icon'])
                        ->map(function ($category) {
                            return [
                                'id' => $category->id,
                                'name' => $category->name,
                                'slug' => $category->slug,
                                'icon' => $category->icon_url ?? null,
                            ];
                        });
                } catch (\Exception $e) {
                    return [];
                }
            },
            // Provide global settings for all pages
            'settings' => function () {
                try {
                    return ConfigHelper::globalSettings();
                } catch (\Exception $e) {
                    // Fallback to ConfigHelper methods with defaults
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
            },
        ]);
    }
}