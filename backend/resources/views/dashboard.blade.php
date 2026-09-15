@extends('layouts.app')

@section('title', 'Dashboard - SessionVault')

@section('content')
<div class="card">
    <h1>Dashboard</h1>
    <p>Welcome, {{ auth()->user()->name }}!</p>
    <p>Role: <span class="badge @if(auth()->user()->isAdmin()) badge-admin @else badge-user @endif">{{ auth()->user()->role }}</span></p>
    <p>Email: {{ auth()->user()->email }}</p>
</div>

<div class="card">
    <h2>Quick Links</h2>
    <ul>
        <li><a href="/profile">View/Edit Profile</a></li>
        <li><a href="/orders">View Orders</a></li>
        <li><a href="/sessions">Manage Sessions</a></li>
        @if(auth()->user()->isAdmin())
            <li><a href="/admin/dashboard">Admin Dashboard</a></li>
        @endif
    </ul>
</div>
@endsection
