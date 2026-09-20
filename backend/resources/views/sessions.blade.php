@extends('layouts.app')

@section('title', 'Sessions - SessionVault')

@section('content')
<style>
    .page-head { align-items:end; display:flex; justify-content:space-between; gap:20px; margin-bottom:28px; }
    .page-head h1 { font-size:38px; letter-spacing:-.06em; margin-top:8px; }
    .page-head p { color:var(--muted); font-size:13px; margin-top:8px; max-width:46ch; }
    .metric-row { display:grid; gap:14px; grid-template-columns:repeat(3,1fr); margin-bottom:20px; }
    .metric { background:#fff; border:1px solid var(--line); border-radius:12px; padding:18px 20px; }
    .metric span { color:var(--muted); display:block; font:500 11px 'DM Mono',monospace; text-transform:uppercase; }
    .metric strong { display:block; font-size:22px; letter-spacing:-.04em; margin-top:10px; }
    .metric code { font-size:13px; }
    .ua { color:var(--muted); display:block; font-size:11px; margin-top:4px; max-width:280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .card-head { align-items:center; display:flex; justify-content:space-between; gap:16px; margin-bottom:4px; }
    .empty { color:var(--muted); padding:28px 0 8px; text-align:center; }
    @media (max-width:700px) {
        .page-head { align-items:flex-start; flex-direction:column; }
        .metric-row { grid-template-columns:1fr; }
        .card-head { align-items:flex-start; flex-direction:column; }
    }
</style>

<div class="page-head">
    <div>
        <div class="eyebrow">Device security</div>
        <h1>Active sessions</h1>
        <p>Review every place your account is signed in. Revoke anything you do not recognize.</p>
    </div>
    <span class="badge badge-user">{{ $sessions->count() }} {{ Str::plural('session', $sessions->count()) }}</span>
</div>

<div class="metric-row">
    <div class="metric">
        <span>Current fingerprint</span>
        <strong><code>{{ $currentSession->fingerprint ?? 'N/A' }}</code></strong>
    </div>
    <div class="metric">
        <span>This device</span>
        <strong>{{ $currentSession->ip_address ?? 'Unknown' }}</strong>
    </div>
    <div class="metric">
        <span>Other sessions</span>
        <strong>{{ max(0, $sessions->count() - ($currentSession ? 1 : 0)) }}</strong>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <div>
            <div class="eyebrow">Signed-in devices</div>
            <h2 style="margin-top:7px;">Where you are logged in</h2>
        </div>
        @if($sessions->count() > 1)
            <form method="POST" action="{{ route('sessions.revoke-all') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-secondary">Revoke all other sessions</button>
            </form>
        @endif
    </div>

    @if($sessions->isEmpty())
        <p class="empty">No active sessions found for this account.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Fingerprint</th>
                    <th>Created</th>
                    <th>Last activity</th>
                    <th>IP / client</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sessions as $session)
                <tr>
                    <td><code>{{ $session->fingerprint }}</code></td>
                    <td>{{ optional($session->created_at)->format('M d, Y H:i') ?? '—' }}</td>
                    <td>{{ optional($session->last_activity)->format('M d, Y H:i') ?? '—' }}</td>
                    <td>
                        {{ $session->ip_address ?: '—' }}
                        @if($session->user_agent)
                            <span class="ua" title="{{ $session->user_agent }}">{{ $session->user_agent }}</span>
                        @endif
                    </td>
                    <td>
                        @if($session->is_current)
                            <span class="badge badge-admin">Current</span>
                        @else
                            <span class="badge badge-user">Active</span>
                        @endif
                    </td>
                    <td>
                        @if(! $session->is_current)
                            <form method="POST" action="{{ route('sessions.revoke', $session->id) }}" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Revoke</button>
                            </form>
                        @else
                            <span class="muted">This device</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
