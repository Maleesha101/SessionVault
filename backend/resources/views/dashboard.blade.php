@extends('layouts.app')

@section('title', 'Dashboard - SessionVault')

@section('content')
<style>
    .dashboard-head { align-items:end; display:flex; justify-content:space-between; margin-bottom:30px; }
    .dashboard-head h1 { font-size:38px; letter-spacing:-.06em; margin-top:8px; }
    .dashboard-head p { color:var(--muted); font-size:13px; margin-top:8px; }
    .overview-grid { display:grid; gap:16px; grid-template-columns:repeat(3,1fr); margin-bottom:22px; }
    .metric { background:#fff; border:1px solid var(--line); border-radius:12px; padding:20px; }
    .metric span { color:var(--muted); display:block; font:500 11px 'DM Mono',monospace; text-transform:uppercase; }
    .metric strong { display:block; font-size:27px; letter-spacing:-.05em; margin-top:10px; }
    .metric small { color:var(--green); display:block; font-size:11px; margin-top:3px; }
    .quick-grid { display:grid; gap:12px; grid-template-columns:repeat(3,1fr); margin-top:20px; }
    .quick-link { border:1px solid var(--line); border-radius:9px; padding:16px; transition:border-color .15s, transform .15s; }
    .quick-link:hover { border-color:var(--green); transform:translateY(-2px); }
    .quick-link strong { display:block; font-size:13px; margin-bottom:4px; }
    .quick-link span { color:var(--muted); font-size:12px; }
    @media (max-width:700px) { .dashboard-head { align-items:flex-start; flex-direction:column; gap:15px; } .overview-grid, .quick-grid { grid-template-columns:1fr; } }
</style>
<div class="dashboard-head"><div><div class="eyebrow">Member overview</div><h1>Good to see you, {{ auth()->user()->name }}.</h1><p>Your SessionVault account is ready when you are.</p></div><span class="badge @if(auth()->user()->isAdmin()) badge-admin @else badge-user @endif">{{ auth()->user()->role }} account</span></div>
<div class="overview-grid"><div class="metric"><span>Account email</span><strong style="font-size:16px; margin-top:14px;">{{ auth()->user()->email }}</strong><small>Verified contact</small></div><div class="metric"><span>Account status</span><strong>Active</strong><small>All services available</small></div><div class="metric"><span>Security</span><strong>Review</strong><small>Manage your sessions →</small></div></div>
<div class="card"><div class="eyebrow">Your space</div><h2 style="margin-top:7px;">Take care of the details</h2><div class="quick-grid"><a class="quick-link" href="/orders"><strong>Orders & deliveries</strong><span>Track purchases and view receipts →</span></a><a class="quick-link" href="/sessions"><strong>Active sessions</strong><span>See where your account is signed in →</span></a><a class="quick-link" href="/profile"><strong>Profile details</strong><span>Keep your contact information current →</span></a>@if(auth()->user()->isAdmin())<a class="quick-link" href="/admin/dashboard"><strong>Admin console</strong><span>Review lab activity and users →</span></a>@endif</div></div>
@endsection
