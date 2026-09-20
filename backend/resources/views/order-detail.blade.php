@extends('layouts.app')

@section('title', 'Order #' . $order->id . ' - SessionVault')

@section('content')
<style>
    .page-head { align-items:end; display:flex; justify-content:space-between; gap:20px; margin-bottom:28px; }
    .page-head h1 { font-size:38px; letter-spacing:-.06em; margin-top:8px; }
    .page-head p { color:var(--muted); font-size:13px; margin-top:8px; }
    .detail-grid { display:grid; gap:16px; grid-template-columns:repeat(4,1fr); margin-bottom:22px; }
    .metric { background:#fff; border:1px solid var(--line); border-radius:12px; padding:20px; }
    .metric span { color:var(--muted); display:block; font:500 11px 'DM Mono',monospace; text-transform:uppercase; }
    .metric strong { display:block; font-size:18px; letter-spacing:-.04em; margin-top:10px; }
    .card-head p { color:var(--muted); font-size:13px; margin-top:6px; }
    @media (max-width:900px) {
        .page-head { align-items:flex-start; flex-direction:column; }
        .detail-grid { grid-template-columns:repeat(2,1fr); }
    }
    @media (max-width:600px) { .detail-grid { grid-template-columns:1fr; } }
</style>

<div class="page-head">
    <div>
        <div class="eyebrow">Order detail</div>
        <h1>Order #{{ $order->id }}</h1>
        <p>Receipt and line items for this purchase.</p>
    </div>
    <div style="display:flex;gap:10px;align-items:center;">
        <span class="badge badge-user">{{ $order->status }}</span>
        <a href="{{ route('orders.index') }}" class="btn btn-secondary">Back to orders</a>
    </div>
</div>

<div class="detail-grid">
    <div class="metric">
        <span>Total</span>
        <strong>${{ number_format($order->total_amount, 2) }}</strong>
    </div>
    <div class="metric">
        <span>Status</span>
        <strong>{{ ucfirst($order->status) }}</strong>
    </div>
    <div class="metric">
        <span>Ordered</span>
        <strong>{{ $order->order_date?->format('M d, Y H:i') ?? '—' }}</strong>
    </div>
    <div class="metric">
        <span>Shipped</span>
        <strong>{{ $order->shipped_date?->format('M d, Y H:i') ?? 'Not yet shipped' }}</strong>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <div class="eyebrow">Line items</div>
        <h2 style="margin-top:7px;">Products in this order</h2>
        <p>{{ $order->items->count() }} {{ $order->items->count() === 1 ? 'item' : 'items' }} on this receipt.</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product->name ?? 'Unknown product' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>${{ number_format($item->unit_price, 2) }}</td>
                <td>${{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
