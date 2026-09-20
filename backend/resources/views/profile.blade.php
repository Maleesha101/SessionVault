@extends('layouts.app')

@section('title', 'Profile - SessionVault')

@section('content')
<style>
    .page-head { align-items:end; display:flex; justify-content:space-between; gap:20px; margin-bottom:28px; }
    .page-head h1 { font-size:38px; letter-spacing:-.06em; margin-top:8px; }
    .page-head p { color:var(--muted); font-size:13px; margin-top:8px; max-width:48ch; }
    .profile-layout { display:grid; gap:20px; grid-template-columns:1.2fr .8fr; }
    .meta-list { display:grid; gap:14px; margin-top:18px; }
    .meta-item { border-top:1px solid var(--line); padding-top:14px; }
    .meta-item span { color:var(--muted); display:block; font:500 11px 'DM Mono',monospace; text-transform:uppercase; }
    .meta-item strong { display:block; font-size:15px; margin-top:6px; }
    .form-actions { display:flex; gap:10px; margin-top:8px; }
    @media (max-width:800px) {
        .page-head { align-items:flex-start; flex-direction:column; }
        .profile-layout { grid-template-columns:1fr; }
    }
</style>

<div class="page-head">
    <div>
        <div class="eyebrow">Account settings</div>
        <h1>Profile details</h1>
        <p>Keep your name and email current so order notices and security alerts reach you.</p>
    </div>
    <span class="badge @if($user->isAdmin()) badge-admin @else badge-user @endif">{{ $user->role }} account</span>
</div>

<div class="profile-layout">
    <div class="card">
        <div class="eyebrow">Edit contact</div>
        <h2 style="margin-top:7px;">Update your details</h2>
        <p class="muted" style="font-size:13px;margin:8px 0 22px;">Changes apply to your SessionVault account immediately.</p>

        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/profile">
            @csrf
            <div class="form-group">
                <label for="name">Full name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save changes</button>
                <a href="/dashboard" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="eyebrow">Account summary</div>
        <h2 style="margin-top:7px;">{{ $user->name }}</h2>
        <p class="muted" style="font-size:13px;margin-top:6px;">{{ $user->email }}</p>

        <div class="meta-list">
            <div class="meta-item">
                <span>Role</span>
                <strong>{{ ucfirst($user->role) }}</strong>
            </div>
            <div class="meta-item">
                <span>Status</span>
                <strong>
                    @if($user->is_disabled)
                        <span class="badge badge-disabled">Disabled</span>
                    @else
                        <span class="badge badge-user">Active</span>
                    @endif
                </strong>
            </div>
            <div class="meta-item">
                <span>Member since</span>
                <strong>{{ $user->created_at?->format('M d, Y') ?? '—' }}</strong>
            </div>
        </div>

        <div style="margin-top:24px;">
            <a href="/sessions" class="btn btn-secondary">Review active sessions</a>
        </div>
    </div>
</div>
@endsection
