<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\DatabaseSeeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Mechanical Keyboard',
                'description' => 'Full-size mechanical keyboard with RGB backlighting and hot-swappable switches.',
                'price' => 129.99,
                'stock_quantity' => 42,
                'image_url' => 'https://images.local/products/keyboard-01.png',
            ],
            [
                'name' => 'Wireless Mouse',
                'description' => 'Ergonomic wireless mouse with adjustable DPI and long battery life.',
                'price' => 49.99,
                'stock_quantity' => 120,
                'image_url' => 'https://images.local/products/mouse-01.png',
            ],
            [
                'name' => 'USB-C Hub',
                'description' => '7-in-1 USB-C hub with HDMI, Ethernet, and SD card reader.',
                'price' => 39.99,
                'stock_quantity' => 65,
                'image_url' => 'https://images.local/products/hub-01.png',
            ],
            [
                'name' => 'Mechanical Keyboard Mousepad',
                'description' => 'Extended gaming mousepad with stitched edges and non-slip base.',
                'price' => 19.99,
                'stock_quantity' => 200,
                'image_url' => 'https://images.local/products/mousepad-01.png',
            ],
            [
                'name' => 'Noise-Cancelling Headphones',
                'description' => 'Over-ear active noise-cancelling headphones with 30-hour battery.',
                'price' => 199.99,
                'stock_quantity' => 18,
                'image_url' => 'https://images.local/products/headphones-01.png',
            ],
            [
                'name' => 'Webcam 1080p',
                'description' => 'HD 1080p webcam with auto-focus and built-in microphone.',
                'price' => 59.99,
                'stock_quantity' => 88,
                'image_url' => 'https://images.local/products/webcam-01.png',
            ],
            [
                'name' => 'USB-C Cable 2m',
                'description' => 'Braided USB-C to USB-C cable supporting 100W charging.',
                'price' => 12.99,
                'stock_quantity' => 500,
                'image_url' => 'https://images.local/products/cable-01.png',
            ],
            [
                'name' => 'External SSD 1TB',
                'description' => 'Portable 1TB solid-state drive with USB 3.2 Gen 2 interface.',
                'price' => 119.99,
                'stock_quantity' => 33,
                'image_url' => 'https://images.local/products/ssd-01.png',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
