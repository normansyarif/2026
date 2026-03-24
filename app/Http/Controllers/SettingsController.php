<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Todo;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        return view('pages.settings', [
            'timezone' => config('app.timezone'),
            'habitCount' => Todo::query()->count(),
            'startDate' => AppSetting::getValue('timeline_start_date'),
            'deadlineDate' => AppSetting::getValue('timeline_deadline_date'),
            'loginUsername' => AppSetting::getValue('login_username'),
            'loginConfigured' => (bool) (AppSetting::getValue('login_username') && AppSetting::getValue('login_password_hash')),
        ]);
    }

    public function updateTimeline(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'start_date' => ['nullable', 'date'],
            'deadline_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        if (filled($data['start_date'] ?? null)) {
            AppSetting::setValue('timeline_start_date', Carbon::parse($data['start_date'])->toDateString());
        } else {
            AppSetting::query()->where('key', 'timeline_start_date')->delete();
        }

        if (filled($data['deadline_date'] ?? null)) {
            AppSetting::setValue('timeline_deadline_date', Carbon::parse($data['deadline_date'])->toDateString());
        } else {
            AppSetting::query()->where('key', 'timeline_deadline_date')->delete();
        }

        return redirect()
            ->route('settings.index')
            ->with('status', 'Timeline settings updated.');
    }

    public function updateLogin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login_username' => ['required', 'string', 'max:120'],
            'login_password' => ['required', 'string', 'min:4'],
        ]);

        AppSetting::setValue('login_username', trim($data['login_username']));
        AppSetting::setValue('login_password_hash', Hash::make($data['login_password']));

        $request->session()->put('app_authenticated', true);

        return redirect()
            ->route('settings.index')
            ->with('status', 'Login credential updated.');
    }
}
