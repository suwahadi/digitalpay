export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                'sans': ['Poppins', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'Noto Sans', 'sans-serif'],
            },
            colors: {
                bluegray: '#1e293b',
            },
            animation: {
                'slide-infinite-left': 'slide-left 30s linear infinite',
                'slide-infinite-right': 'slide-right 30s linear infinite',
                'fade-in-fast': 'fadeIn 0.3s ease-in-out',
                'slit-in-horizontal-fast': 'slitInHorizontal 0.5s ease-out',
            },
            keyframes: {
                'slide-left': {
                    '0%': { transform: 'translateX(0)' },
                    '100%': { transform: 'translateX(-100%)' },
                },
                'slide-right': {
                    '0%': { transform: 'translateX(-100%)' },
                    '100%': { transform: 'translateX(0)' },
                },
                fadeIn: {
                    '0%': { opacity: '0', transform: 'translateY(10px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                slitInHorizontal: {
                    '0%': { transform: 'translateZ(-800px) rotateY(90deg)', opacity: '0' },
                    '54%': { transform: 'translateZ(-160px) rotateY(87deg)', opacity: '1' },
                    '100%': { transform: 'translateZ(0) rotateY(0)', opacity: '1' },
                },
            },
        },
    },
    plugins: [],
}
