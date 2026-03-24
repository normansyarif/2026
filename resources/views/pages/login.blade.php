<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | {{ config('app.name', 'Be A Better You') }}</title>
    <style>
        :root {
            --ink: #1b251f;
            --muted: #5e6a63;
            --accent: #165946;
            --line: rgba(27, 37, 31, 0.12);
            --surface: rgba(255, 252, 245, 0.92);
            --danger: #9c4234;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            font-family: "Segoe UI", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(22, 89, 70, 0.18), transparent 30%),
                radial-gradient(circle at bottom right, rgba(184, 128, 31, 0.16), transparent 26%),
                linear-gradient(155deg, #f7f1e5 0%, #efe5d3 52%, #f3ece0 100%);
        }

        .login-card {
            width: min(100%, 420px);
            padding: 28px;
            border-radius: 28px;
            background: var(--surface);
            border: 1px solid var(--line);
            box-shadow: 0 28px 80px rgba(29, 39, 34, 0.14);
            display: grid;
            gap: 18px;
        }

        h1 {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 2.2rem;
            letter-spacing: -0.03em;
        }

        p {
            margin: 0;
            color: var(--muted);
            line-height: 1.7;
        }

        .field {
            display: grid;
            gap: 8px;
        }

        .field label {
            font-weight: 600;
        }

        .input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 13px 14px;
            background: rgba(255, 255, 255, 0.86);
            font: inherit;
        }

        .button {
            border: 0;
            border-radius: 999px;
            padding: 12px 18px;
            background: var(--accent);
            color: #fff9f0;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }

        .flash {
            padding: 14px 16px;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: rgba(255,255,255,0.72);
        }

        .flash.error {
            color: var(--danger);
            border-color: rgba(156, 66, 52, 0.2);
            background: rgba(254, 245, 242, 0.92);
        }
    </style>
</head>
<body>
    <main class="login-card">
        <div>
            <h1>Login</h1>
            <p>Enter the app credential to continue.</p>
        </div>

        @if (session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="flash error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" style="display:grid; gap:16px;">
            @csrf

            <div class="field">
                <label for="username">Username</label>
                <input id="username" class="input" type="text" name="username" value="{{ old('username', $loginUsername) }}" autocomplete="username">
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" class="input" type="password" name="password" autocomplete="current-password">
            </div>

            <button type="submit" class="button">Log in</button>
        </form>
    </main>
</body>
</html>
