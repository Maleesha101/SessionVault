@extends('layouts.app')

@section('title', 'Admin Dashboard - SessionVault')

@section('content')
<div class="card">
    <h1>Admin Dashboard</h1>
    <p>Welcome, {{ auth()->user()->name }}. Here you can manage users and sessions.</p>
</div>

<div class="card">
    <h2>Users</h2>
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
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td><span class="badge badge-user">{{ $user->role }}</span></td>
                <td>@if($user->is_disabled)<span class="badge badge-disabled">Disabled</span>@else<span class="badge badge-user">Active</span>@endif</td>
                <td>
                    @if(! $user->is_disabled)
                        <form method="POST" action="/admin/users/{{ $user->id }}/disable" style="display:inline">
                            @csrf
                            @method('POST')
                            <button type="submit" class="btn btn-danger">Disable</button>
                        </form>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Recent Sessions</h2>
    <table>
        <thead>
            <tr>
                <th>Session ID</th>
                <th>User</th>
                <th>Last Activity</th>
                <th>Fingerprint</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sessions as $session)
            <tr>
                <td><code>{{ $session->id }}</code></td>
                <td>{{ $session->user_name }}</td>
                <td>{{ $session->last_activity->format('M d, Y H:i') }}</td>
                <td><code>{{ $session->fingerprint }}</code></td>
                <td>{{ $session->created_at->format('M d, Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
