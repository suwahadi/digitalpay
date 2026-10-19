<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ $page['props']['title'] ?? \App\Helpers\ConfigHelper::siteName() }}</title>

    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    
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
    <script src="https://code.iconify.design/3/3.1.1/iconify.min.js"></script>
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>