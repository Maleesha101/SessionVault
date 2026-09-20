<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class CartService
{
    public const SESSION_KEY = 'sessionvault_bag';

    public static function items(): Collection
    {
        $bag = Session::get(self::SESSION_KEY, []);

        if ($bag === []) {
            return collect();
        }

        $products = Product::whereIn('id', array_keys($bag))->get()->keyBy('id');

        return collect($bag)
            ->map(function ($quantity, $productId) use ($products) {
                $product = $products->get((int) $productId);

                if (! $product) {
                    return null;
                }

                $qty = max(1, (int) $quantity);

                return (object) [
                    'product' => $product,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $product->price,
                    'line_total' => $product->price * $qty,
                ];
            })
            ->filter()
            ->values();
    }

    public static function count(): int
    {
        return (int) collect(Session::get(self::SESSION_KEY, []))->sum();
    }

    public static function total(): float
    {
        return (float) self::items()->sum('line_total');
    }

    public static function add(int $productId, int $quantity = 1): void
    {
        $bag = Session::get(self::SESSION_KEY, []);
        $bag[$productId] = ($bag[$productId] ?? 0) + max(1, $quantity);
        Session::put(self::SESSION_KEY, $bag);
    }

    public static function update(int $productId, int $quantity): void
    {
        $bag = Session::get(self::SESSION_KEY, []);

        if ($quantity <= 0) {
            unset($bag[$productId]);
        } else {
            $bag[$productId] = $quantity;
        }

        Session::put(self::SESSION_KEY, $bag);
    }

    public static function remove(int $productId): void
    {
        $bag = Session::get(self::SESSION_KEY, []);
        unset($bag[$productId]);
        Session::put(self::SESSION_KEY, $bag);
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public static function checkoutPayload(): array
    {
        return self::items()
            ->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
            ])
            ->all();
    }
}
