@extends('layouts.app')

@section('page_eyebrow', 'App Space')
@section('page_title', 'Settings')
@section('page_intro', 'Set the timeline that drives the Today counters, and manage the app login credential that protects the rest of the tracker.')

@section('content')
    <section class="card">
        <h2>Current setup</h2>
        <p>These cards summarize the current app defaults and the pieces that are already active in the tracker.</p>

        <div class="stats-grid">
            <div class="stat">
                <span class="stat-label">Timezone</span>
                <span class="stat-value">{{ $timezone }}</span>
            </div>
            <div class="stat">
                <span class="stat-label">Total habits</span>
                <span class="stat-value">{{ $habitCount }}</span>
            </div>
            <div class="stat">
                <span class="stat-label">Login protection</span>
                <span class="stat-value">{{ $loginConfigured ? 'Enabled' : 'Not set' }}</span>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="stack">
            <h2>Timeline</h2>
            <p>The start date powers the week counter, and the deadline powers the day countdown on the Today page.</p>
        </div>

        <form method="POST" action="{{ route('settings.timeline.update') }}" class="field-grid" style="margin-top: 18px;">
            @csrf

            <div class="field-row">
                <div class="field">
                    <label class="field-label" for="start_date">Starting date</label>
                    <input id="start_date" class="input" type="date" name="start_date" value="{{ old('start_date', $startDate) }}">
                </div>

                <div class="field">
                    <label class="field-label" for="deadline_date">Deadline</label>
                    <input id="deadline_date" class="input" type="date" name="deadline_date" value="{{ old('deadline_date', $deadlineDate) }}">
                </div>
            </div>

            <div class="button-row">
                <button type="submit" class="button">Save timeline</button>
            </div>
        </form>
    </section>

    <section class="card">
        <div class="stack">
            <h2>Login credential</h2>
            <p>Once a username and password are saved here, the app will require login until the browser cookie is cleared or you explicitly log out.</p>
        </div>

        <form method="POST" action="{{ route('settings.login.update') }}" class="field-grid" style="margin-top: 18px;">
            @csrf

            <div class="field">
                <label class="field-label" for="login_username">Username</label>
                <input id="login_username" class="input" type="text" name="login_username" value="{{ old('login_username', $loginUsername) }}" autocomplete="username">
            </div>

            <div class="field">
                <label class="field-label" for="login_password">Password</label>
                <input id="login_password" class="input" type="password" name="login_password" autocomplete="new-password">
            </div>

            <div class="button-row">
                <button type="submit" class="button">Save login</button>
            </div>
        </form>
    </section>
@endsection
