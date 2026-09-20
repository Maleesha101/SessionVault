@extends('layouts.app')

@section('title', $product->name . ' - SessionVault')

@section('content')
<style>
    .page-head { margin-bottom:28px; }
    .page-head a { color:var(--green); font-size:12px; font-weight:800; }
    .detail { display:grid; gap:20px; grid-template-columns:1fr 1.1fr; }
    .detail-art { align-items:center; background:#eaf3ed; border:1px solid var(--line); border-radius:14px; color:var(--green); display:flex; font:500 72px 'DM Mono',monospace; justify-content:center; min-height:360px; }
    .detail-card { background:#fff; border:1px solid var(--line); border-radius:14px; box-shadow:var(--shadow); padding:28px; }
    .detail-card h1 { font-size:34px; letter-spacing:-.05em; margin-top:8px; }
    .detail-card .desc { color:var(--muted); font-size:14px; margin:14px 0 22px; }
    .price { font:500 28px 'DM Mono',monospace; }
    .stock { color:var(--muted); font-size:12px; margin-top:6px; }
    .buy-row { align-items:end; display:flex; gap:12px; margin-top:24px; }
    .buy-row .form-group { margin:0; max-width:100px; }
    @media (max-width:800px) { .detail { grid-template-columns:1fr; } .detail-art { min-height:220px; } }
</style>

<div class="page-head">
    <a href="{{ route('products.index') }}">← Back to collection</a>
</div>

<div class="detail">
    <div class="detail-art">{{ strtoupper(substr($product->name, 0, 2)) }}</div>
    <div class="detail-card">
        <div class="eyebrow">Product</div>
        <h1>{{ $product->name }}</h1>
        <p class="desc">{{ $product->description }}</p>
        <div class="price">${{ number_format($product->price, 2) }}</div>
        <div class="stock">{{ $product->stock_quantity }} available</div>

        <form method="POST" action="{{ route('bag.add', $product->id) }}" class="buy-row">
            @csrf
            <div class="form-group">
                <label for="quantity">Qty</label>
                <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ max(1, $product->stock_quantity) }}">
            </div>
            <button type="submit" class="btn btn-primary">Add to bag</button>
            <a href="{{ route('bag.show') }}" class="btn btn-secondary">View bag</a>
        </form>
    </div>
</div>
@endsection
