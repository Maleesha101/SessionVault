@extends('layouts.app')

@section('title', 'Orders - SessionVault')

@section('content')
<div class="card">
    <h1>My Orders</h1>
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
                <td>{{ $order->id }}</td>
                <td>${{ number_format($order->total_amount, 2) }}</td>
                <td><span class="badge badge-user">{{ $order->status }}</span></td>
                <td>{{ $order->order_date->format('M d, Y') }}</td>
                <td>{{ count($order->items) }}</td>
                <td><a href="/orders/{{ $order->id }}">View</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
