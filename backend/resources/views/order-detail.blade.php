@extends('layouts.app')

@section('title', 'Order Detail - SessionVault')

@section('content')
<div class="card">
    <h1>Order #{{ $order->id }}</h1>
    <p>Status: <span class="badge badge-user">{{ $order->status }}</span></p>
    <p>Total: ${{ number_format($order->total_amount, 2) }}</p>
    <p>Order Date: {{ $order->order_date->format('M d, Y H:i') }}</p>
    <p>Shipped: @if($order->shipped_date){{ $order->shipped_date->format('M d, Y H:i') }}@else(Not yet shipped)@endif</p>
</div>

<div class="card">
    <h2>Order Items</h2>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>${{ number_format($item->unit_price, 2) }}</td>
                <td>${{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
