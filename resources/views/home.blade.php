<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Be A Better You') }}</title>
    <meta name="description" content="A minimal Laravel + MariaDB starter for a habit tracking progressive web app.">
    <meta name="theme-color" content="#17624a">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" href="{{ asset('icons/icon-192.png') }}" sizes="192x192" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">
    <style>
        :root {
            color-scheme: light;
            --surface: rgba(255, 252, 244, 0.88);
            --surface-strong: #fffdf7;
            --text: #1f2a24;
            --muted: #5b675f;
            --accent: #17624a;
            --accent-soft: #d9eee5;
            --border: rgba(31, 42, 36, 0.12);
            --shadow: 0 24px 60px rgba(41, 55, 48, 0.14);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Georgia, "Times New Roman", serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(23, 98, 74, 0.16), transparent 34%),
                radial-gradient(circle at bottom right, rgba(177, 113, 48, 0.18), transparent 28%),
                linear-gradient(160deg, #f8f4ea 0%, #efe7d6 48%, #efe9df 100%);
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .shell {
            width: min(760px, 100%);
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 28px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(12px);
            overflow: hidden;
        }

        .hero {
            padding: 32px 28px 20px;
            border-bottom: 1px solid var(--border);
            background:
                linear-gradient(135deg, rgba(23, 98, 74, 0.08), transparent 55%),
                linear-gradient(0deg, rgba(255, 255, 255, 0.72), rgba(255, 255, 255, 0.72));
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 0.82rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--accent);
            background: var(--accent-soft);
        }

        .eyebrow::before {
            content: "";
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--accent);
        }

        h1 {
            margin: 18px 0 10px;
            font-size: clamp(2.2rem, 5vw, 4rem);
            line-height: 0.95;
            letter-spacing: -0.04em;
        }

        p {
            margin: 0;
            color: var(--muted);
            font-family: "Segoe UI", sans-serif;
            font-size: 1rem;
            line-height: 1.7;
        }

        .content {
            display: grid;
            gap: 18px;
            padding: 28px;
        }

        .panel {
            padding: 20px;
            border-radius: 20px;
            border: 1px solid var(--border);
            background: var(--surface-strong);
        }

        .panel h2 {
            margin: 0 0 10px;
            font-size: 1.1rem;
            font-family: "Segoe UI", sans-serif;
        }

        .list {
            margin: 0;
            padding-left: 18px;
            color: var(--muted);
            font-family: "Segoe UI", sans-serif;
            line-height: 1.8;
        }

        code {
            font-family: Consolas, "Courier New", monospace;
            font-size: 0.95em;
            background: #edf3ef;
            border-radius: 6px;
            padding: 2px 6px;
            color: #164836;
        }

        .footer {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            padding: 0 28px 28px;
            font-family: "Segoe UI", sans-serif;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .badge {
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.72);
        }

        @media (max-width: 640px) {
            .hero,
            .content,
            .footer {
                padding-left: 20px;
                padding-right: 20px;
            }
        }
    </style>
</head>
<body>
    <main class="shell">
        <section class="hero">
            <span class="eyebrow">Laravel PWA Starter</span>
            <h1>{{ config('app.name', 'Be A Better You') }} is ready.</h1>
            <p>
                This project is set up with Laravel, MariaDB configuration, and a minimal progressive web app shell.
                We can add the actual habit features next without revisiting the foundation.
            </p>
        </section>

        <section class="content">
            <article class="panel">
                <h2>Included right now</h2>
                <ul class="list">
                    <li>Laravel app scaffolded and ready for Blade-based development</li>
                    <li>MariaDB connection values prepared in <code>.env</code></li>
                    <li>Manifest, service worker, and install icons added for PWA support</li>
                    <li>Lightweight file-based session and cache defaults for a mini project</li>
                </ul>
            </article>

            <article class="panel">
                <h2>Good next step</h2>
                <p>
                    Tell me the first feature you want, and I'll build it directly on top of this starter.
                </p>
            </article>
        </section>

        <footer class="footer">
            <div class="badge">Route: <code>/</code></div>
            <div class="badge">Database: <code>mariadb</code></div>
            <div class="badge">PWA: <code>manifest + service worker</code></div>
        </footer>
    </main>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register(@json(asset('sw.js')));
            });
        }
    </script>
</body>
</html>
