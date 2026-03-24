<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $__env->yieldContent('page_title', config('app.name', 'Be A Better You')).' | '.config('app.name', 'Be A Better You') }}</title>
    <meta name="description" content="A lightweight Laravel progressive web app for recurring daily habits.">
    <meta name="theme-color" content="#165946">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="{{ url('/manifest.webmanifest') }}">
    <link rel="icon" href="{{ url('/icons/icon-192.png') }}" sizes="192x192" type="image/png">
    <link rel="apple-touch-icon" href="{{ url('/icons/apple-touch-icon.png') }}">
    <style>
        :root {
            color-scheme: light;
            --ink: #1b251f;
            --muted: #5e6a63;
            --accent: #165946;
            --accent-strong: #0f4435;
            --accent-soft: #dceee7;
            --paper: #f7f1e5;
            --surface: rgba(255, 252, 245, 0.86);
            --surface-strong: #fffdf8;
            --line: rgba(27, 37, 31, 0.12);
            --shadow: 0 28px 80px rgba(29, 39, 34, 0.14);
            --danger: #9c4234;
            --warning: #b8801f;
            --success: #1d6c44;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(22, 89, 70, 0.18), transparent 30%),
                radial-gradient(circle at bottom right, rgba(184, 128, 31, 0.16), transparent 26%),
                linear-gradient(155deg, #f7f1e5 0%, #efe5d3 52%, #f3ece0 100%);
        }

        h1, h2, h3 {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            letter-spacing: -0.03em;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input {
            font: inherit;
        }

        .app-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(0, 1fr);
        }

        .backdrop {
            position: fixed;
            inset: 0;
            background: rgba(19, 26, 22, 0.44);
            opacity: 0;
            pointer-events: none;
            transition: opacity 180ms ease;
            z-index: 30;
        }

        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: min(82vw, 280px);
            padding: 22px 18px;
            background: rgba(18, 34, 28, 0.94);
            color: #f6f1e6;
            transform: translateX(-100%);
            transition: transform 200ms ease;
            z-index: 40;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        body[data-menu-open="true"] .sidebar {
            transform: translateX(0);
        }

        body[data-menu-open="true"] .backdrop {
            opacity: 1;
            pointer-events: auto;
        }

        .brand {
            padding: 14px 14px 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.14);
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 0.82rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #c6e2d8;
        }

        .brand-mark::before {
            content: "";
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #6dd0a6;
        }

        .brand h1 {
            margin-top: 10px;
            font-size: 2rem;
        }

        .nav-list {
            display: grid;
            gap: 8px;
        }

        .sidebar-spacer {
            flex: 1;
        }

        .nav-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 18px;
            color: rgba(246, 241, 230, 0.82);
            border: 1px solid transparent;
            transition: transform 160ms ease, background 160ms ease, color 160ms ease;
        }

        .nav-link:hover,
        .nav-link:focus-visible {
            transform: translateX(2px);
            background: rgba(255, 255, 255, 0.06);
            color: #fff7ec;
            outline: none;
        }

        .nav-link.active {
            background: linear-gradient(135deg, rgba(109, 208, 166, 0.26), rgba(255, 255, 255, 0.08));
            border-color: rgba(198, 226, 216, 0.24);
            color: #fffaf0;
        }

        .nav-kicker {
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(198, 226, 216, 0.84);
        }

        .nav-label {
            font-size: 1rem;
            font-weight: 600;
        }

        .nav-copy {
            display: grid;
            gap: 2px;
        }

        .main-column {
            width: min(1100px, 100%);
            margin: 0 auto;
            padding: 18px 16px 36px;
        }

        .topbar {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 18px;
        }

        .menu-button {
            appearance: none;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.7);
            color: var(--ink);
            border-radius: 18px;
            padding: 12px 14px;
            min-width: 52px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 10px 24px rgba(36, 46, 41, 0.08);
        }

        .menu-icon {
            width: 20px;
            height: 14px;
            display: grid;
            gap: 4px;
        }

        .menu-icon span {
            display: block;
            height: 2px;
            border-radius: 999px;
            background: currentColor;
        }

        .title-block {
            flex: 1;
            padding: 10px 4px 0;
        }

        .eyebrow {
            margin-bottom: 6px;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--accent);
        }

        .page-title {
            font-size: clamp(2rem, 5vw, 3.6rem);
            line-height: 0.95;
        }

        .page-intro {
            margin-top: 10px;
            color: var(--muted);
            line-height: 1.7;
            max-width: 56rem;
        }

        .flash-stack {
            display: grid;
            gap: 12px;
            margin-bottom: 18px;
        }

        .flash {
            padding: 14px 16px;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: var(--surface);
            box-shadow: 0 18px 40px rgba(35, 46, 40, 0.08);
        }

        .flash.success {
            border-color: rgba(29, 108, 68, 0.22);
            background: rgba(240, 251, 245, 0.86);
        }

        .flash.error {
            border-color: rgba(156, 66, 52, 0.22);
            background: rgba(254, 245, 242, 0.9);
        }

        .error-list {
            margin: 8px 0 0;
            padding-left: 18px;
            color: var(--danger);
            line-height: 1.7;
        }

        .content-stack {
            display: grid;
            gap: 18px;
        }

        .card {
            padding: 22px;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 24px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(14px);
        }

        .card h2 {
            font-size: 1.6rem;
            margin-bottom: 8px;
        }

        .card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.7;
        }

        .button-row,
        .meta-row,
        .chip-row,
        .stats-grid,
        .split-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .split-grid {
            align-items: flex-start;
            justify-content: space-between;
        }

        .button,
        .button-secondary,
        .button-danger {
            appearance: none;
            border-radius: 999px;
            border: 1px solid transparent;
            padding: 11px 18px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 160ms ease, background 160ms ease, border-color 160ms ease;
        }

        .button {
            background: var(--accent);
            color: #fff9f0;
            box-shadow: 0 14px 30px rgba(22, 89, 70, 0.2);
        }

        .button:hover,
        .button-secondary:hover,
        .button-danger:hover {
            transform: translateY(-1px);
        }

        .button-secondary {
            background: rgba(255, 255, 255, 0.72);
            border-color: var(--line);
            color: var(--ink);
        }

        .button-danger {
            background: rgba(156, 66, 52, 0.08);
            border-color: rgba(156, 66, 52, 0.2);
            color: var(--danger);
        }

        .field-grid {
            display: grid;
            gap: 16px;
        }

        .field-row {
            display: grid;
            gap: 16px;
        }

        .field {
            display: grid;
            gap: 8px;
        }

        .field-label {
            font-size: 0.92rem;
            font-weight: 600;
            color: var(--ink);
        }

        .field-help {
            margin-top: -4px;
            font-size: 0.88rem;
            color: var(--muted);
        }

        .input,
        .number-input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.86);
            padding: 13px 14px;
            color: var(--ink);
            outline: none;
        }

        .input:focus,
        .number-input:focus {
            border-color: rgba(22, 89, 70, 0.5);
            box-shadow: 0 0 0 4px rgba(22, 89, 70, 0.1);
        }

        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .day-option {
            position: relative;
        }

        .day-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .day-label {
            display: block;
            border-radius: 16px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.8);
            padding: 12px 14px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
        }

        .day-option input:checked + .day-label {
            background: var(--accent-soft);
            border-color: rgba(22, 89, 70, 0.3);
            color: var(--accent-strong);
        }

        .chip,
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 0.9rem;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.72);
            color: var(--muted);
        }

        .pill.success {
            background: rgba(221, 244, 230, 0.9);
            color: var(--success);
            border-color: rgba(29, 108, 68, 0.16);
        }

        .pill.warning {
            background: rgba(250, 242, 219, 0.92);
            color: var(--warning);
            border-color: rgba(184, 128, 31, 0.14);
        }

        .pill.neutral {
            color: var(--muted);
        }

        .stats-grid {
            margin-top: 14px;
        }

        .stat {
            min-width: 140px;
            padding: 14px;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.68);
        }

        .stat-label {
            display: block;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .stat-value {
            font-size: 1.4rem;
            font-family: Georgia, "Times New Roman", serif;
        }

        .progress-track {
            width: 100%;
            height: 12px;
            border-radius: 999px;
            background: rgba(22, 89, 70, 0.1);
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #1c7e62, #7fd2ae);
        }

        .stack {
            display: grid;
            gap: 14px;
        }

        .hidden-panel {
            display: none;
        }

        .hidden-panel.is-open {
            display: block;
        }

        .empty-state {
            text-align: center;
            padding: 30px 20px;
        }

        .danger-form {
            margin: 0;
        }

        @media (min-width: 900px) {
            .app-shell {
                grid-template-columns: 280px minmax(0, 1fr);
            }

            .sidebar {
                position: sticky;
                top: 0;
                transform: none;
                width: 100%;
                min-height: 100vh;
            }

            .backdrop,
            .menu-button {
                display: none;
            }

            .main-column {
                padding: 28px 28px 42px 8px;
            }

            .field-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .checkbox-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }
    </style>
    @stack('page_styles')
</head>
<body data-menu-open="false">
    @php
        $navItems = [
            ['route' => 'today.index', 'match' => 'today.*', 'kicker' => 'Daily', 'label' => 'Today'],
            ['route' => 'habits.index', 'match' => 'habits.*', 'kicker' => 'Manage', 'label' => 'Habits'],
            ['route' => 'goals.index', 'match' => 'goals.*', 'kicker' => 'Plan', 'label' => 'Goals'],
            ['route' => 'weight-loss.index', 'match' => 'weight-loss.*', 'kicker' => 'Health', 'label' => 'Weight Loss'],
            ['route' => 'settings.index', 'match' => 'settings.*', 'kicker' => 'App', 'label' => 'Settings'],
        ];
    @endphp

    <div class="app-shell">
        <div class="backdrop" data-menu-close></div>

        <aside class="sidebar" id="app-sidebar">
            <div class="brand">
                <h1>{{ config('app.name', 'Be A Better You') }}</h1>
            </div>

            <nav class="nav-list" aria-label="Primary">
                @foreach ($navItems as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        class="nav-link {{ request()->routeIs($item['match'] ?? $item['route']) ? 'active' : '' }}"
                    >
                        <span class="nav-copy">
                            <span class="nav-kicker">{{ $item['kicker'] }}</span>
                            <span class="nav-label">{{ $item['label'] }}</span>
                        </span>
                    </a>
                @endforeach
            </nav>

            <div class="sidebar-spacer"></div>

            @if (session('app_authenticated') === true)
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="button-danger" style="width: 100%;">Log out</button>
                </form>
            @endif
        </aside>

        <div class="main-column">
            <header class="topbar">
                <button type="button" class="menu-button" data-menu-toggle aria-expanded="false" aria-controls="app-sidebar">
                    <span class="menu-icon" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </header>

            <div class="flash-stack">
                @if (session('status'))
                    <div class="flash success">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="flash error">
                        There are a couple of things to fix before we can save that.
                        <ul class="error-list">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <main class="content-stack">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        (function () {
            const body = document.body;
            const toggle = document.querySelector('[data-menu-toggle]');
            const closeTargets = document.querySelectorAll('[data-menu-close]');

            function setMenu(open) {
                body.dataset.menuOpen = open ? 'true' : 'false';
                if (toggle) {
                    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                }
            }

            if (toggle) {
                toggle.addEventListener('click', function () {
                    setMenu(body.dataset.menuOpen !== 'true');
                });
            }

            closeTargets.forEach(function (element) {
                element.addEventListener('click', function () {
                    setMenu(false);
                });
            });

            document.querySelectorAll('[data-panel-toggle]').forEach(function (button) {
                const panel = document.getElementById(button.dataset.panelToggle);

                if (!panel) {
                    return;
                }

                if (panel.dataset.open === 'true') {
                    panel.classList.add('is-open');
                    button.setAttribute('aria-expanded', 'true');
                } else {
                    button.setAttribute('aria-expanded', 'false');
                }

                button.addEventListener('click', function () {
                    const willOpen = !panel.classList.contains('is-open');
                    panel.classList.toggle('is-open', willOpen);
                    button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                });
            });

            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function () {
                    navigator.serviceWorker.register(@json(url('/sw.js')));
                });
            }
        })();
    </script>
    @stack('page_scripts')
</body>
</html>
