@extends('layouts.app')

@section('title', 'Legacy Profile - SessionVault')

@section('content')
<div class="card">
    <h1>Legacy Profile Page</h1>
    <div class="alert alert-warning">
        <strong>WARNING (SM-09):</strong> This page accepts session tokens in the URL query string.
        This is intentionally vulnerable and demonstrates why session tokens should never be placed in URLs.
    </div>
    <p>Session token detected in URL: <code>{{ $session ?? 'None' }}</code></p>
    <p>Session fingerprint: <code>{{ $session ? substr(hash('sha256', $session), 0, 12) : 'N/A' }}</code></p>
    <p><strong>Note:</strong> The legitimate application uses cookie-based authentication. This legacy route is
    maintained only for educational purposes to demonstrate URL-based session token vulnerabilities.</p>
</div>
@endsection
