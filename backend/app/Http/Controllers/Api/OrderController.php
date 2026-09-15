<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\SecurityEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Get the current user's orders.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'UNAUTHENTICATED',
            ], 401);
        }

        $orders = Order::where('user_id', $user->id)
            ->with(['items.product'])
            ->latest('order_date')
            ->get();

        return response()->json([
            'orders' => $orders->transform(function ($order) {
                return [
                    'id' => $order->id,
                    'total_amount' => $order->total_amount,
                    'status' => $order->status,
                    'order_date' => $order->order_date,
                    'shipped_date' => $order->shipped_date,
                    'items' => $order->items->transform(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'total_price' => $item->total_price,
                        ];
                    }),
                ];
            }),
        ]);
    }

    /**
     * Get an individual order's details.
     */
    public function show(Request $request, int $orderId): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'UNAUTHENTICATED',
            ], 401);
        }

        $order = Order::where('user_id', $user->id)
            ->with(['items.product'])
            ->find($orderId);

        if (! $order) {
            return response()->json([
                'message' => 'Order not found',
                'error' => 'ORDER_NOT_FOUND',
            ], 404);
        }

        return response()->json([
            'order' => [
                'id' => $order->id,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'order_date' => $order->order_date,
                'shipped_date' => $order->shipped_date,
                'items' => $order->items->transform(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Create a new order.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'error' => 'UNAUTHENTICATED',
            ], 401);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:99',
        ]);

        $products = Product::whereIn('id', array_keys($validated['items']))->get();
        $productsById = $products->keyBy('id');

        $total = 0;
        $orderItems = [];

        foreach ($validated['items'] as $itemData) {
            $product = $productsById[$itemData['product_id']] ?? null;

            if (! $product) {
                return response()->json([
                    'message' => 'Product not found',
                    'error' => 'PRODUCT_NOT_FOUND',
                ], 404);
            }

            if ($product->stock_quantity < $itemData['quantity']) {
                return response()->json([
                    'message' => 'Insufficient stock for product: ' . $product->name,
                    'error' => 'INSUFFICIENT_STOCK',
                ], 400);
            }

            $total += $itemData['quantity'] * $product->price;

            $orderItems[] = [
                'product_id' => $product->id,
                'quantity' => $itemData['quantity'],
                'unit_price' => $product->price,
                'total_price' => $product->price * $itemData['quantity'],
            ];

            // Update stock
            $product->decrement('stock_quantity', $itemData['quantity']);
        }

        $order = Order::create([
            'user_id' => $user->id,
            'total_amount' => $total,
            'status' => 'pending',
            'order_date' => now(),
        ]);

        foreach ($orderItems as $itemData) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'total_price' => $itemData['total_price'],
            ]);
        }

        SecurityEventService::log('ORDER_CREATED', $user->id, null, [
            'order_id' => $order->id,
            'item_count' => count($orderItems),
            'total' => $total,
        ]);

        return response()->json([
            'message' => 'Order created successfully',
            'order' => [
                'id' => $order->id,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'items' => $orderItems,
            ],
        ], 201);
    }
}