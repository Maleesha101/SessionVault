@extends('layouts.app')

@section('title', 'XSS Demo - SessionVault')

@section('content')
<div class="card">
    <h1>XSS Demonstration Page</h1>
    <div class="alert alert-warning">
        <strong>Educational Only:</strong> This page demonstrates how XSS can be used to steal session cookies.
        This is restricted to the local lab environment. No external data is sent.
    </div>
    <h2>Cookie Inspector</h2>
    <p>Open your browser DevTools to inspect cookies:</p>
    <pre>{{ json_encode(request()->cookies->all(), JSON_PRETTY_PRINT) }}</pre>
    <h2>Security Advisory</h2>
    <p>The session cookie should have the HttpOnly flag set to prevent JavaScript access.
    Inspect the Set-Cookie header in the Network tab to verify.</p>
</div>
@endsection
