export const routes = {
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
    'logout': '/logout',
}

export function route(name, params = {}) {
    const url = routes[name]

    if (!url) {
        return `/${name}`
    }

    let finalUrl = url
    for (const [key, value] of Object.entries(params)) {
        const encoded = encodeURIComponent(value)
        finalUrl = finalUrl.replace(`{${key}:slug}`, encoded).replace(`{${key}}`, encoded)
    }

    return finalUrl
}
