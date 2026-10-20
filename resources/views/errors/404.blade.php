<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Halaman tidak ditemukan &mdash; {{ \App\Helpers\ConfigHelper::siteName() }}</title>
    <link rel="icon" href="{{ \App\Helpers\ConfigHelper::siteFavicon() }}">
    <style>
        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url('/fonts/poppins-400.woff2') format('woff2');
        }
        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 700;
            font-display: swap;
            src: url('/fonts/poppins-700.woff2') format('woff2');
        }
        :root { --bg: #f9fafb; --surface: #ffffff; --text: #111827; --muted: #6b7280; --brand: #2563eb; --brand-ink: #ffffff; --brand-soft: #eff6ff; --border: #e5e7eb; }
        @media (prefers-color-scheme: dark) {
            :root { --bg: #0b1120; --surface: #111827; --text: #f3f4f6; --muted: #9ca3af; --brand: #3b82f6; --brand-ink: #0b1120; --brand-soft: rgba(59, 130, 246, 0.12); --border: #1f2937; }
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; height: 100%; }
        body {
            font-family: 'Poppins', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 480px;
            text-align: center;
        }
        .logo-link { display: inline-block; margin-bottom: 32px; }
        .logo { display: block; height: 40px; }
        .code {
            font-size: clamp(72px, 22vw, 120px);
            font-weight: 700;
            line-height: 1;
            color: var(--brand);
            letter-spacing: -0.03em;
        }
        h1 { font-size: 20px; font-weight: 700; margin: 16px 0 8px; }
        p { color: var(--muted); margin: 0 auto 28px; max-width: 34ch; line-height: 1.6; }
        .actions { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 12px;
            border-radius: 10px;
            font-weight: 500;
            text-decoration: none;
            border: 2px solid var(--brand);
            transition: background .2s, border-color .2s;
        }
        .btn-primary { background: var(--brand); color: var(--brand-ink); }
        .btn-primary:hover { filter: brightness(1.08); }
        .btn-ghost { color: var(--brand); background: var(--surface); }
        .btn-ghost:hover { background: var(--brand-soft); }
    </style>
</head>
<body>
    <main class="card">
        <a href="/" class="logo-link"><img class="logo" src="{{ \App\Helpers\ConfigHelper::siteLogo() }}" alt="{{ \App\Helpers\ConfigHelper::siteName() }}" onerror="this.onerror=null;this.src='/images/logo.png'"></a>
        <div class="code">404</div>
        <h1>Halaman tidak ditemukan</h1>
        <p>Halaman yang kamu cari mungkin sudah dipindahkan atau tidak pernah ada. Yuk kembali dan lanjutkan dari sini.</p>
        <div class="actions">
            <a class="btn btn-primary" href="/">Halaman Utama</a>
            <a class="btn btn-ghost" href="/products">Lihat Produk</a>
        </div>
    </main>
</body>
</html>
