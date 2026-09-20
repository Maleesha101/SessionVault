@extends('layouts.app')

@section('title', 'Products - SessionVault')

@section('content')
<style>
    .page-head { align-items:end; display:flex; justify-content:space-between; gap:20px; margin-bottom:28px; }
    .page-head h1 { font-size:38px; letter-spacing:-.06em; margin-top:8px; }
    .page-head p { color:var(--muted); font-size:13px; margin-top:8px; max-width:48ch; }
    .page-actions { display:flex; gap:10px; flex-wrap:wrap; }
    .product-grid { display:grid; gap:16px; grid-template-columns:repeat(3,1fr); }
    .product { background:#fff; border:1px solid var(--line); border-radius:12px; overflow:hidden; display:flex; flex-direction:column; }
    .product-image { align-items:center; background:#eaf3ed; display:flex; font:500 42px 'DM Mono',monospace; height:170px; justify-content:center; color:var(--green); }
    .product:nth-child(3n+2) .product-image { background:#f8eadc; color:#9b5c20; }
    .product:nth-child(3n) .product-image { background:#e5e8f2; color:#245a91; }
    .product-body { display:flex; flex:1; flex-direction:column; padding:18px; }
    .product-body h3 { font-size:16px; letter-spacing:-.03em; }
    .product-body p { color:var(--muted); font-size:12px; margin:5px 0 15px; flex:1; }
    .product-meta { align-items:center; display:flex; justify-content:space-between; gap:10px; }
    .price { font:500 15px 'DM Mono',monospace; }
    .stock { color:var(--muted); display:block; font-size:11px; margin-top:4px; }
    .mini-btn { background:var(--ink); border:0; border-radius:6px; color:#fff; cursor:pointer; font:700 11px Manrope,sans-serif; padding:9px 11px; }
    .empty { background:#fff; border:1px solid var(--line); border-radius:12px; color:var(--muted); padding:40px; text-align:center; }
    @media (max-width:900px) { .product-grid { grid-template-columns:repeat(2,1fr); } }
    @media (max-width:700px) {
        .page-head { align-items:flex-start; flex-direction:column; }
        .product-grid { grid-template-columns:1fr; }
    }
</style>

<div class="page-head">
    <div>
        <div class="eyebrow">The collection</div>
        <h1>Browse products</h1>
        <p>Everyday essentials from the SessionVault lab storefront. Add items to your bag and check out when you are ready.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('bag.show') }}" class="btn btn-secondary">Bag ({{ $bagCount }})</a>
        @auth
            <a href="{{ route('orders.index') }}" class="btn btn-primary">Your orders</a>
        @endauth
    </div>
</div>

@if($products->isEmpty())
    <div class="empty">No products are available yet. Seed the catalog to populate this page.</div>
@else
    <div class="product-grid">
        @foreach($products as $product)
            <article class="product">
                <a href="{{ route('products.show', $product->id) }}" class="product-image">
                    {{ strtoupper(substr($product->name, 0, 2)) }}
                </a>
                <div class="product-body">
                    <h3><a href="{{ route('products.show', $product->id) }}">{{ $product->name }}</a></h3>
                    <p>{{ $product->description }}</p>
                    <div class="product-meta">
                        <div>
                            <span class="price">${{ number_format($product->price, 2) }}</span>
                            <span class="stock">{{ $product->stock_quantity }} in stock</span>
                        </div>
                        <form method="POST" action="{{ route('bag.add', $product->id) }}">
                            @csrf
                            <input type="hidden" name="redirect" value="{{ route('products.index') }}">
                            <button type="submit" class="mini-btn">Add to bag</button>
                        </form>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
@endif
@endsection
