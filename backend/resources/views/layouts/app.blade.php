<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SessionVault')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#17211d; --muted:#68756f; --line:#dfe6e1; --paper:#f6f8f4; --white:#fff; --green:#176b52; --mint:#dff3e8; --orange:#e47d48; --shadow:0 18px 50px rgba(25,48,39,.08); }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { background:var(--paper); color:var(--ink); font-family:Manrope, sans-serif; line-height:1.5; }
        a { color:inherit; text-decoration:none; }
        .security-banner { background:#17211d; color:#dff3e8; padding:8px 24px; text-align:center; font:500 11px 'DM Mono', monospace; letter-spacing:.08em; text-transform:uppercase; }
        .header { align-items:center; background:rgba(246,248,244,.94); border-bottom:1px solid var(--line); display:flex; justify-content:space-between; min-height:76px; padding:0 max(24px, calc((100vw - 1180px) / 2)); position:relative; z-index:2; }
        .brand { align-items:center; display:flex; gap:11px; font-size:17px; font-weight:800; letter-spacing:-.04em; }
        .brand-mark { align-items:center; background:var(--green); border-radius:10px; color:#fff; display:flex; font:500 13px 'DM Mono', monospace; height:32px; justify-content:center; width:32px; }
        .nav { align-items:center; display:flex; gap:26px; }
        .nav a { color:var(--muted); font-size:13px; font-weight:700; }
        .nav a:hover, .nav a.active { color:var(--green); }
        .nav .nav-cta { background:var(--green); border-radius:7px; color:#fff; padding:10px 15px; }
        .container { margin:0 auto; max-width:1180px; padding:48px 24px 72px; }
        .card { background:var(--white); border:1px solid var(--line); border-radius:14px; box-shadow:var(--shadow); margin-bottom:20px; padding:28px; }
        .card h1, .card h2 { letter-spacing:-.045em; }
        .card h1 { font-size:32px; line-height:1.1; }
        .card h2 { font-size:20px; }
        .eyebrow { color:var(--green); font:500 11px 'DM Mono', monospace; letter-spacing:.12em; text-transform:uppercase; }
        .muted { color:var(--muted); }
        .alert { border-radius:8px; margin-bottom:20px; padding:14px 16px; }
        .alert-danger { background:#fff0ed; border:1px solid #f4c8bd; color:#a6432e; }
        .alert-success { background:var(--mint); border:1px solid #b7dec9; color:#176b52; }
        .alert-warning { background:#fff5e9; border:1px solid #f0d3ae; color:#9b5c20; }
        .badge { border-radius:99px; display:inline-block; font:500 11px 'DM Mono', monospace; padding:5px 9px; text-transform:uppercase; }
        .badge-admin { background:#e3f0ff; color:#245a91; } .badge-user { background:var(--mint); color:var(--green); } .badge-disabled { background:#fff0ed; color:#a6432e; }
        table { border-collapse:collapse; margin-top:20px; width:100%; } th,td { border-bottom:1px solid var(--line); padding:15px 10px; text-align:left; } th { color:var(--muted); font:500 11px 'DM Mono', monospace; text-transform:uppercase; } td { font-size:13px; }
        tr:last-child td { border-bottom:0; } tr:hover td { background:#fbfdfb; }
        code { color:var(--green); font-family:'DM Mono', monospace; font-size:11px; }
        .btn { border:0; border-radius:7px; cursor:pointer; display:inline-block; font:700 12px Manrope, sans-serif; padding:11px 16px; transition:transform .15s, opacity .15s; } .btn:hover { opacity:.88; transform:translateY(-1px); } .btn-primary { background:var(--green); color:#fff; } .btn-danger { background:#a6432e; color:#fff; } .btn-secondary { background:#e8eeea; color:var(--ink); }
        .form-group { margin-bottom:18px; } .form-group label { display:block; font-size:12px; font-weight:800; margin-bottom:7px; } .form-group input { background:#fbfdfb; border:1px solid var(--line); border-radius:7px; color:var(--ink); font:14px Manrope,sans-serif; outline:0; padding:12px 13px; width:100%; } .form-group input:focus { border-color:var(--green); box-shadow:0 0 0 3px #dff3e8; }
        @media (max-width:700px) { .header { align-items:flex-start; flex-direction:column; gap:18px; padding:18px 20px; } .nav { flex-wrap:wrap; gap:12px 18px; } .container { padding:28px 16px 50px; } .card { padding:20px; overflow-x:auto; } table { min-width:620px; } }
    </style>
    @yield('styles')
</head>
<body>
    @if(env('APP_ENV') === 'local')<div class="security-banner">Security training lab · intentionally vulnerable environment · local only</div>@endif
    <header class="header">
        <a href="/" class="brand"><span class="brand-mark">SV</span> SessionVault</a>
        <nav class="nav">
            @if(auth()->check())
                <a href="/dashboard">Overview</a><a href="/orders">Orders</a><a href="/sessions">Sessions</a><a href="/profile">Profile</a>
                @if(auth()->user()->isAdmin())<a href="/admin/dashboard">Admin</a>@endif
                <a href="/logout" class="nav-cta">Sign out</a>
            @else
                <a href="/login">Sign in</a><a href="/register" class="nav-cta">Create account</a>
            @endif
        </nav>
    </header>
    <main class="container">
        @if(session('message'))<div class="alert alert-success">{{ session('message') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @yield('content')
    </main>
</body>
</html>
