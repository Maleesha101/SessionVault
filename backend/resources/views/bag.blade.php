@extends('layouts.app')

@section('title', 'Your bag - SessionVault')

@section('content')
<style>
    .page-head { align-items:end; display:flex; justify-content:space-between; gap:20px; margin-bottom:28px; }
    .page-head h1 { font-size:38px; letter-spacing:-.06em; margin-top:8px; }
    .page-head p { color:var(--muted); font-size:13px; margin-top:8px; max-width:48ch; }
    .bag-layout { display:grid; gap:20px; grid-template-columns:1.4fr .8fr; }
    .qty-form { align-items:center; display:flex; gap:8px; }
    .qty-form input { width:72px; }
    .summary-row { align-items:center; border-top:1px solid var(--line); display:flex; justify-content:space-between; margin-top:14px; padding-top:14px; }
    .summary-row strong { font-size:22px; letter-spacing:-.04em; }
    .empty { color:var(--muted); padding:36px 0 12px; text-align:center; }
    .empty a { color:var(--green); font-weight:700; }
    @media (max-width:800px) {
        .page-head { align-items:flex-start; flex-direction:column; }
        .bag-layout { grid-template-columns:1fr; }
    }
</style>

<div class="page-head">
    <div>
        <div class="eyebrow">Checkout prep</div>
        <h1>Your bag</h1>
        <p>Review quantities, then place an order. Signed-in members can check out directly to the orders page.</p>
    </div>
    <a href="{{ route('products.index') }}" class="btn btn-secondary">Browse collection</a>
</div>

@if($items->isEmpty())
    <div class="card">
        <p class="empty">Your bag is empty. <a href="{{ route('products.index') }}">Browse the collection</a> to add products.</p>
    </div>
@else
    <div class="bag-layout">
        <div class="card">
            <div class="eyebrow">Items</div>
            <h2 style="margin-top:7px;">Ready to order</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td>
                            <a href="{{ route('products.show', $item->product_id) }}" style="font-weight:700;">{{ $item->product->name }}</a>
                        </td>
                        <td>${{ number_format($item->unit_price, 2) }}</td>
                        <td>
                            <form method="POST" action="{{ route('bag.update', $item->product_id) }}" class="qty-form">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="99">
                                <button type="submit" class="btn btn-secondary" style="padding:8px 10px;">Update</button>
                            </form>
                        </td>
                        <td>${{ number_format($item->line_total, 2) }}</td>
                        <td>
                            <form method="POST" action="{{ route('bag.remove', $item->product_id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="padding:8px 10px;">Remove</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card">
            <div class="eyebrow">Summary</div>
            <h2 style="margin-top:7px;">Order total</h2>
            <p class="muted" style="font-size:13px;margin-top:8px;">{{ $bagCount }} {{ $bagCount === 1 ? 'item' : 'items' }} in your bag.</p>
            <div class="summary-row">
                <span class="muted">Total</span>
                <strong>${{ number_format($total, 2) }}</strong>
            </div>

            @auth
                <form method="POST" action="{{ route('orders.store') }}" style="margin-top:22px;">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="width:100%;">Place order</button>
                </form>
                <a href="{{ route('orders.index') }}" class="btn btn-secondary" style="display:block;margin-top:10px;text-align:center;">View orders</a>
            @else
                <p class="muted" style="font-size:13px;margin-top:18px;">Sign in to place this order.</p>
                <a href="{{ route('login') }}" class="btn btn-primary" style="display:block;margin-top:12px;text-align:center;">Sign in to checkout</a>
                <a href="{{ route('register') }}" class="btn btn-secondary" style="display:block;margin-top:10px;text-align:center;">Create account</a>
            @endauth
        </div>
    </div>
@endif
@endsection
