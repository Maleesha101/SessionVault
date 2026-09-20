@extends('layouts.app')

@section('title', 'Orders - SessionVault')

@section('content')
<style>
    .page-head { align-items:end; display:flex; justify-content:space-between; gap:20px; margin-bottom:28px; }
    .page-head h1 { font-size:38px; letter-spacing:-.06em; margin-top:8px; }
    .page-head p { color:var(--muted); font-size:13px; margin-top:8px; max-width:48ch; }
    .metric-row { display:grid; gap:16px; grid-template-columns:repeat(3,1fr); margin-bottom:22px; }
    .metric { background:#fff; border:1px solid var(--line); border-radius:12px; padding:20px; }
    .metric span { color:var(--muted); display:block; font:500 11px 'DM Mono',monospace; text-transform:uppercase; }
    .metric strong { display:block; font-size:27px; letter-spacing:-.05em; margin-top:10px; }
    .metric small { color:var(--green); display:block; font-size:11px; margin-top:6px; }
    .card-head p { color:var(--muted); font-size:13px; margin-top:6px; }
    .empty { color:var(--muted); padding:36px 0 12px; text-align:center; }
    .row-link { color:var(--green); font-weight:700; }
    @media (max-width:700px) {
        .page-head { align-items:flex-start; flex-direction:column; }
        .metric-row { grid-template-columns:1fr; }
    }
</style>

<div class="page-head">
    <div>
        <div class="eyebrow">Purchases</div>
        <h1>Orders & deliveries</h1>
        <p>Track your purchases, check status, and open receipts for each order.</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <span class="badge badge-user">{{ $orders->count() }} {{ $orders->count() === 1 ? 'order' : 'orders' }}</span>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Browse collection</a>
        <a href="{{ route('bag.show') }}" class="btn btn-primary">View bag</a>
    </div>
</div>

<div class="metric-row">
    <div class="metric">
        <span>Total orders</span>
        <strong>{{ $orders->count() }}</strong>
        <small>All time</small>
    </div>
    <div class="metric">
        <span>Pending</span>
        <strong>{{ $orders->where('status', 'pending')->count() }}</strong>
        <small>Awaiting fulfillment</small>
    </div>
    <div class="metric">
        <span>Spend</span>
        <strong style="font-size:22px;">${{ number_format($orders->sum('total_amount'), 2) }}</strong>
        <small>Across all orders</small>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <div class="eyebrow">Order history</div>
        <h2 style="margin-top:7px;">Your purchases</h2>
        <p>Select an order to see line items and shipping details.</p>
    </div>

    @if($orders->isEmpty())
        <p class="empty">No orders yet. <a class="row-link" href="{{ route('products.index') }}">Browse the collection</a> or <a class="row-link" href="{{ route('bag.show') }}">open your bag</a> to place one.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td><code>#{{ $order->id }}</code></td>
                    <td>${{ number_format($order->total_amount, 2) }}</td>
                    <td><span class="badge badge-user">{{ $order->status }}</span></td>
                    <td>{{ $order->order_date?->format('M d, Y') ?? '—' }}</td>
                    <td>{{ $order->items->count() }}</td>
                    <td><a class="row-link" href="{{ route('orders.show', $order->id) }}">View →</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
