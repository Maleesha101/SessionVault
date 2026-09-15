<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SessionVault')</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        .header {
            background: #1a1a2e;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header a { color: #4fc3f7; text-decoration: none; margin-left: 1rem; }
        .header a:hover { text-decoration: underline; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        .card { background: white; border-radius: 8px; padding: 1.5rem; margin-bottom: 1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .alert { padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
        .alert-danger { background: #ffebee; color: #c62828; border-left: 4px solid #c62828; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #2e7d32; }
        .alert-warning { background: #fff3e0; color: #e65100; border-left: 4px solid #e65100; }
        .badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold; }
        .badge-admin { background: #e3f2fd; color: #1565c0; }
        .badge-user { background: #f3e5f5; color: #7b1fa2; }
        .badge-disabled { background: #ffcdd2; color: #c62828; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f5f5f5; font-weight: 600; }
        tr:hover { background: #fafafa; }
        .btn { display: inline-block; padding: 0.5rem 1rem; border-radius: 4px; border: none; cursor: pointer; font-size: 0.9rem; }
        .btn-primary { background: #1565c0; color: white; }
        .btn-danger { background: #c62828; color: white; }
        .btn-secondary { background: #757575; color: white; }
        .btn:hover { opacity: 0.9; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        .form-group input { width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem; }
        .security-banner { background: #ffcdd2; color: #c62828; padding: 0.5rem 1rem; text-align: center; font-weight: bold; font-size: 0.9rem; }
    </style>
    @yield('styles')
</head>
<body>
    @if(env('APP_ENV') === 'local')
        <div class="security-banner">
            SECURITY TRAINING LAB — Intentionally Vulnerable Environment
        </div>
    @endif
    <div class="header">
        <div>
            <a href="/"><strong>SessionVault</strong></a>
        </div>
        <div>
            @if(auth()->check())
                <a href="/dashboard">Dashboard</a>
                <a href="/profile">Profile</a>
                <a href="/orders">Orders</a>
                <a href="/sessions">Sessions</a>
                @if(auth()->user()->isAdmin())
                    <a href="/admin/dashboard">Admin</a>
                @endif
                <a href="/logout">Logout</a>
            @else
                <a href="/login">Login</a>
                <a href="/register">Register</a>
            @endif
        </div>
    </div>
    <div class="container">
        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @yield('content')
    </div>
</body>
</html>
