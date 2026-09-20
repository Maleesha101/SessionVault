<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function products(): View
    {
        $products = Product::query()
            ->orderBy('name')
            ->get();

        return view('products', [
            'products' => $products,
            'bagCount' => CartService::count(),
        ]);
    }

    public function show(int $productId): View
    {
        $product = Product::findOrFail($productId);

        return view('product-detail', [
            'product' => $product,
            'bagCount' => CartService::count(),
        ]);
    }

    public function bag(): View
    {
        return view('bag', [
            'items' => CartService::items(),
            'total' => CartService::total(),
            'bagCount' => CartService::count(),
        ]);
    }

    public function addToBag(Request $request, int $productId): RedirectResponse
    {
        $product = Product::findOrFail($productId);
        $quantity = max(1, (int) $request->input('quantity', 1));

        if ($product->stock_quantity < $quantity) {
            return back()->with('error', 'Not enough stock for '.$product->name.'.');
        }

        CartService::add($product->id, $quantity);

        return redirect()
            ->to($request->input('redirect', '/bag'))
            ->with('message', $product->name.' added to your bag.');
    }

    public function updateBag(Request $request, int $productId): RedirectResponse
    {
        $quantity = (int) $request->input('quantity', 1);
        CartService::update($productId, $quantity);

        return redirect('/bag')->with('message', 'Bag updated.');
    }

    public function removeFromBag(int $productId): RedirectResponse
    {
        CartService::remove($productId);

        return redirect('/bag')->with('message', 'Item removed from your bag.');
    }
}
