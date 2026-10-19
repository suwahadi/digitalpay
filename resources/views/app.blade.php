<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ $page['props']['title'] ?? \App\Helpers\ConfigHelper::siteName() }}</title>

    <!-- Poppins - self hosted -->
    <link rel="preload" as="font" type="font/woff2" href="/fonts/poppins-400.woff2" crossorigin>

    @php $heroImage = $page['props']['hero']['slides'][0]['image'] ?? null; @endphp
    @if($heroImage)
        <link rel="preload" as="image" href="{{ $heroImage }}" fetchpriority="high">
    @endif
    
    <!-- Scripts -->
    <script>
        window.routes = {
            'login': '/login',
            'register': '/register',
            'home': '/',
            'news.index': '/news',
            'news.show': '/news/{slug}',
            'products.index': '/products',
            'products.show': '/product/{slug}',
            'products.category': '/products/category/{category}',
            'checkout.index': '/checkout',
            'checkout.create': '/checkout',
            'checkout.store': '/checkout/store',
            'invoice.show': '/invoice/{orderNumber}',
            'orders.guest.create': '/orders/guest',
            'orders.guest.track': '/orders/track/{token}',
            'user.dashboard': '/user',
            'user.history': '/user/history',
            'user.profile': '/user/profile',
            'user.order.detail': '/user/order/{orderNumber}',
            'logout': '/logout'
        };
    </script>

    @vite('resources/js/app.js')
    @inertiaHead

    <!-- Iconify -->
    <script src="https://code.iconify.design/3/3.1.1/iconify.min.js" async></script>
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>