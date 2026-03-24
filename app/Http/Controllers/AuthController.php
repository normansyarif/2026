<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (!$this->credentialsExist()) {
            return redirect()
                ->route('today.index')
                ->with('status', 'Set a username and password in Settings to turn login protection on.');
        }

        if (session('app_authenticated') === true) {
            return redirect()->route('today.index');
        }

        return view('pages.login', [
            'loginUsername' => AppSetting::getValue('login_username'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->credentialsExist(), 404);

        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $configuredUsername = AppSetting::getValue('login_username');
        $configuredPasswordHash = AppSetting::getValue('login_password_hash');

        if (
            $data['username'] !== $configuredUsername ||
            !Hash::check($data['password'], (string) $configuredPasswordHash)
        ) {
            return back()
                ->withErrors(['username' => 'The login credentials do not match.'])
                ->onlyInput('username');
        }

        $request->session()->put('app_authenticated', true);
        $request->session()->regenerate();

        return redirect()
            ->route('today.index')
            ->with('status', 'You are logged in.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget('app_authenticated');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login.show')
            ->with('status', 'You have been logged out.');
    }

    private function credentialsExist(): bool
    {
        return (bool) (AppSetting::getValue('login_username') && AppSetting::getValue('login_password_hash'));
    }
}
