<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAppAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $username = AppSetting::getValue('login_username');
        $passwordHash = AppSetting::getValue('login_password_hash');

        if (!$username || !$passwordHash) {
            return $next($request);
        }

        if ($request->session()->get('app_authenticated') === true) {
            return $next($request);
        }

        return redirect()
            ->route('login.show')
            ->with('status', 'Please log in to continue.');
    }
}
