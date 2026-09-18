<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $alice = User::where('email', 'alice@example.local')->first();
        $bob = User::where('email', 'bob@example.local')->first();
        $charlie = User::where('email', 'charlie@example.local')->first();

        $products = Product::all()->keyBy('id');

        $orders = [
            [
                'user' => $alice,
                'total' => 99.98,
                'status' => 'delivered',
                'order_date' => now()->subDays(15),
                'items' => [
                    ['product' => $products[2], 'quantity' => 2, 'unit_price' => 49.99],
                ],
            ],
            [
                'user' => $alice,
                'total' => 129.99,
                'status' => 'delivered',
                'order_date' => now()->subDays(8),
                'items' => [
                    ['product' => $products[1], 'quantity' => 1, 'unit_price' => 129.99],
                ],
            ],
            [
                'user' => $alice,
                'total' => 212.98,
                'status' => 'shipped',
                'order_date' => now()->subDays(3),
                'items' => [
                    ['product' => $products[5], 'quantity' => 1, 'unit_price' => 199.99],
                    ['product' => $products[7], 'quantity' => 1, 'unit_price' => 12.99],
                ],
            ],
            [
                'user' => $bob,
                'total' => 199.99,
                'status' => 'pending',
                'order_date' => now()->subDay(),
                'items' => [
                    ['product' => $products[5], 'quantity' => 1, 'unit_price' => 199.99],
                ],
            ],
            [
                'user' => $charlie,
                'total' => 155.97,
                'status' => 'delivered',
                'order_date' => now()->subDays(22),
                'items' => [
                    ['product' => $products[1], 'quantity' => 1, 'unit_price' => 129.99],
                    ['product' => $products[7], 'quantity' => 2, 'unit_price' => 12.99],
                ],
            ],
        ];

        foreach ($orders as $orderData) {
            $order = Order::create([
                'user_id' => $orderData['user']->id,
                'total_amount' => $orderData['total'],
                'status' => $orderData['status'],
                'order_date' => $orderData['order_date'],
                'shipped_date' => $orderData['status'] === 'shipped' ? now()->subDays(1) : null,
            ]);

            foreach ($orderData['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['unit_price'] * $item['quantity'],
                ]);
            }
        }
    }
}
