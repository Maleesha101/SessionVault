@extends('layouts.app')

@section('title', 'Sessions - SessionVault')

@section('content')
<div class="card">
    <h1>Active Sessions</h1>
    <p>Your current session fingerprint: <code>{{ $currentSession ? $currentSession->fingerprint : 'N/A' }}</code></p>
    <table>
        <thead>
            <tr>
                <th>Fingerprint</th>
                <th>Created</th>
                <th>Last Activity</th>
                <th>IP Address</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sessions as $session)
            <tr>
                <td><code>{{ $session->fingerprint }}</code></td>
                <td>{{ $session->created_at->format('M d, Y H:i') }}</td>
                <td>{{ $session->last_activity->format('M d, Y H:i') }}</td>
                <td>{{ $session->ip_address }}</td>
                <td>@if($session->is_current)<span class="badge badge-user">Current</span>@else<span class="badge badge-user">Active</span>@endif</td>
                <td>
                    @if(! $session->is_current)
                        <form method="POST" action="/sessions/{{ $session->id }}" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Revoke</button>
                        </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($sessions->count() > 1)
<div class="card">
    <h2>Session Management</h2>
    <form method="POST" action="/sessions/all">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-secondary">Revoke All Other Sessions</button>
    </form>
</div>
@endif
@endsection
