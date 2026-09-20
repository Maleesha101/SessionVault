@extends('layouts.app')

@section('title', 'SessionVault · Everyday essentials, securely held')

@section('content')
<style>
    .home { padding-top:18px; } .hero { background:var(--green); border-radius:18px; color:#fff; display:grid; grid-template-columns:1.05fr .95fr; min-height:420px; overflow:hidden; position:relative; } .hero-copy { align-self:center; max-width:560px; padding:62px; position:relative; z-index:1; } .hero .eyebrow { color:#a9e2c5; } .hero h1 { font-size:clamp(42px,6vw,74px); letter-spacing:-.07em; line-height:.98; margin:18px 0 22px; max-width:610px; } .hero p { color:#d4eee0; font-size:16px; max-width:450px; } .hero-art { align-items:center; background:radial-gradient(circle at 52% 45%, #5baf86 0 2px, transparent 3px), radial-gradient(circle at 30% 25%, #4b9d77 0 2px, transparent 3px), #155f49; background-size:28px 28px, 34px 34px, auto; display:flex; justify-content:center; min-height:360px; padding:40px; } .vault-card { background:#effaf3; border-radius:15px; box-shadow:20px 24px 0 rgba(8,48,34,.22); color:var(--ink); padding:26px; transform:rotate(5deg); width:min(300px,100%); } .vault-card .stripe { background:var(--orange); border-radius:4px; height:32px; margin:18px -26px; } .vault-card .chip { border:1px solid #b5c9bc; border-radius:5px; height:26px; width:34px; } .vault-card small { color:var(--muted); display:block; font:11px 'DM Mono',monospace; margin-top:24px; } .hero-actions { display:flex; gap:12px; margin-top:30px; } .hero-actions .btn-primary { background:#f6bb75; color:#17211d; } .hero-actions .btn-secondary { background:transparent; border:1px solid #6ca98a; color:#fff; }
    .section-head { align-items:end; display:flex; justify-content:space-between; margin:52px 0 20px; } .section-head h2 { font-size:27px; letter-spacing:-.05em; } .section-head a { color:var(--green); font-size:12px; font-weight:800; } .product-grid { display:grid; gap:16px; grid-template-columns:repeat(3,1fr); } .product { background:#fff; border:1px solid var(--line); border-radius:12px; overflow:hidden; } .product-image { align-items:center; background:#eaf3ed; display:flex; font:500 42px 'DM Mono',monospace; height:180px; justify-content:center; color:var(--green); } .product:nth-child(2) .product-image { background:#f8eadc; color:#9b5c20; } .product:nth-child(3) .product-image { background:#e5e8f2; color:#245a91; } .product-body { padding:18px; } .product-body h3 { font-size:16px; letter-spacing:-.03em; } .product-body p { color:var(--muted); font-size:12px; margin:5px 0 15px; } .product-meta { align-items:center; display:flex; justify-content:space-between; gap:10px; } .price { font:500 15px 'DM Mono',monospace; } .mini-btn { background:var(--ink); border:0; border-radius:6px; color:#fff; cursor:pointer; font:700 11px Manrope,sans-serif; padding:9px 11px; }
    .trust-row { border-top:1px solid var(--line); display:grid; gap:20px; grid-template-columns:repeat(3,1fr); margin-top:58px; padding-top:25px; } .trust-row strong { display:block; font-size:13px; margin-bottom:3px; } .trust-row span { color:var(--muted); font-size:12px; }
    .empty-note { background:#fff; border:1px solid var(--line); border-radius:12px; color:var(--muted); padding:28px; text-align:center; }
    @media (max-width:700px) { .hero { grid-template-columns:1fr; } .hero-copy { padding:38px 28px 30px; } .hero h1 { font-size:47px; } .hero-art { min-height:245px; } .product-grid, .trust-row { grid-template-columns:1fr; } .section-head { align-items:flex-start; flex-direction:column; gap:6px; } }
</style>
<div class="home">
    <section class="hero">
        <div class="hero-copy">
            <div class="eyebrow">A considered collection for modern living</div>
            <h1>Keep the good things close.</h1>
            <p>Thoughtful essentials, delivered simply. Your private customer space for orders, account details, and every session you have open.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="{{ route('products.index') }}">Browse the collection</a>
                @guest
                    <a class="btn btn-secondary" href="{{ route('login') }}">Sign in</a>
                @else
                    <a class="btn btn-secondary" href="{{ route('orders.index') }}">Your orders</a>
                @endguest
            </div>
        </div>
        <div class="hero-art"><div class="vault-card"><div class="eyebrow">SessionVault / member</div><div class="stripe"></div><div class="chip"></div><small>ACCOUNT ACCESS · PROTECTED LOCALLY</small></div></div>
    </section>

    <div class="section-head">
        <div>
            <div class="eyebrow">Curated essentials</div>
            <h2>Made for the everyday</h2>
        </div>
        <a href="{{ route('products.index') }}">View all products →</a>
    </div>

    @if(($featured ?? collect())->isEmpty())
        <div class="empty-note">Products will appear here once the catalog is seeded. <a href="{{ route('products.index') }}" style="color:var(--green);font-weight:700;">Open the collection</a></div>
    @else
        <div class="product-grid">
            @foreach($featured as $product)
                <article class="product">
                    <a href="{{ route('products.show', $product->id) }}" class="product-image">{{ strtoupper(substr($product->name, 0, 2)) }}</a>
                    <div class="product-body">
                        <h3><a href="{{ route('products.show', $product->id) }}">{{ $product->name }}</a></h3>
                        <p>{{ \Illuminate\Support\Str::limit($product->description, 70) }}</p>
                        <div class="product-meta">
                            <span class="price">${{ number_format($product->price, 2) }}</span>
                            <form method="POST" action="{{ route('bag.add', $product->id) }}">
                                @csrf
                                <input type="hidden" name="redirect" value="{{ url('/') }}">
                                <button type="submit" class="mini-btn">Add to bag</button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    <div class="trust-row">
        <div><strong>Fast, considered dispatch</strong><span>Orders leave our studio within two days.</span></div>
        <div><strong>Your space, at a glance</strong><span>Review active sessions and account activity.</span></div>
        <div><strong>Built for the lab</strong><span>A realistic storefront for security practice.</span></div>
    </div>
</div>
@endsection
