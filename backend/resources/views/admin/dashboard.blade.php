@extends('layouts.app')

@section('title', 'Admin Dashboard - SessionVault')

@section('content')
<style>
    .page-head { align-items:end; display:flex; justify-content:space-between; gap:20px; margin-bottom:28px; }
    .page-head h1 { font-size:38px; letter-spacing:-.06em; margin-top:8px; }
    .page-head p { color:var(--muted); font-size:13px; margin-top:8px; max-width:52ch; }
    .metric-row { display:grid; gap:16px; grid-template-columns:repeat(4,1fr); margin-bottom:22px; }
    .metric { background:#fff; border:1px solid var(--line); border-radius:12px; padding:20px; }
    .metric span { color:var(--muted); display:block; font:500 11px 'DM Mono',monospace; text-transform:uppercase; }
    .metric strong { display:block; font-size:27px; letter-spacing:-.05em; margin-top:10px; }
    .metric small { color:var(--green); display:block; font-size:11px; margin-top:6px; }
    .card-head { margin-bottom:4px; }
    .card-head p { color:var(--muted); font-size:13px; margin-top:6px; }
    .empty { color:var(--muted); padding:28px 0 8px; text-align:center; }
    @media (max-width:900px) {
        .page-head { align-items:flex-start; flex-direction:column; }
        .metric-row { grid-template-columns:repeat(2,1fr); }
    }
    @media (max-width:600px) { .metric-row { grid-template-columns:1fr; } }
</style>

<div class="page-head">
    <div>
        <div class="eyebrow">Lab operations</div>
        <h1>Admin console</h1>
        <p>Welcome, {{ auth()->user()->name }}. Review accounts, disable compromised users, and inspect recent session activity.</p>
    </div>
    <span class="badge badge-admin">Admin access</span>
</div>

<div class="metric-row">
    <div class="metric">
        <span>Total users</span>
        <strong>{{ $stats['users'] }}</strong>
        <small>Registered accounts</small>
    </div>
    <div class="metric">
        <span>Active users</span>
        <strong>{{ $stats['active_users'] }}</strong>
        <small>Not disabled</small>
    </div>
    <div class="metric">
        <span>Open sessions</span>
        <strong>{{ $stats['sessions'] }}</strong>
        <small>Across all users</small>
    </div>
    <div class="metric">
        <span>Admins</span>
        <strong>{{ $stats['admins'] }}</strong>
        <small>Elevated role</small>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <div class="eyebrow">Directory</div>
        <h2 style="margin-top:7px;">Users</h2>
        <p>Disable an account when you need to stop further access from that member.</p>
    </div>

    @if($users->isEmpty())
        <p class="empty">No users found.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td><code>{{ $user->id }}</code></td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge @if($user->role === 'admin') badge-admin @else badge-user @endif">{{ $user->role }}</span>
                    </td>
                    <td>
                        @if($user->is_disabled)
                            <span class="badge badge-disabled">Disabled</span>
                        @else
                            <span class="badge badge-user">Active</span>
                        @endif
                    </td>
                    <td>
                        @if($user->is_disabled)
                            <span class="muted">—</span>
                        @elseif($user->id === auth()->id())
                            <span class="muted">You</span>
                        @else
                            <form method="POST" action="{{ route('admin.users.disable', $user->id) }}" style="display:inline" onsubmit="return confirm('Disable {{ $user->name }}?')">
                                @csrf
                                <button type="submit" class="btn btn-danger">Disable</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="card">
    <div class="card-head">
        <div class="eyebrow">Activity</div>
        <h2 style="margin-top:7px;">Recent sessions</h2>
        <p>Latest signed-in sessions across the lab environment.</p>
    </div>

    @if($sessions->isEmpty())
        <p class="empty">No recent sessions.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Fingerprint</th>
                    <th>User</th>
                    <th>Last activity</th>
                    <th>IP</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sessions as $session)
                <tr>
                    <td><code>{{ $session->fingerprint }}</code></td>
                    <td>
                        {{ $session->user_name }}
                        @if($session->is_current)
                            <span class="badge badge-admin" style="margin-left:6px;">Current</span>
                        @endif
                    </td>
                    <td>{{ $session->last_activity?->format('M d, Y H:i') ?? '—' }}</td>
                    <td>{{ $session->ip_address ?: '—' }}</td>
                    <td>{{ $session->created_at?->format('M d, Y H:i') ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
