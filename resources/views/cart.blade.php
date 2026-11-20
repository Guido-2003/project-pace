@extends('layouts.store')

@section('title', 'Shopping Cart | Project Pace')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/cart.css') }}">
<link rel="stylesheet" href="{{ asset('css/footer.css') }}">
<link rel="stylesheet" href="{{ asset('css/header.css') }}">
<link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
<link rel="stylesheet" href="{{ asset('css/animations.css') }}">
@endsection

@section('content')
<main class="cart-page">

        <div class="cart-heading">
            <p class="eyebrow">
                PROJECT PACE / YOUR SELECTION
            </p>

            <h1>YOUR CART<span>.</span></h1>

            <p>
                Your pieces. Your pace.
            </p>
        </div>

        <div class="cart-layout">

            <section class="cart-items-section"
                     aria-label="Productos en el carrito">

                <div class="cart-list-heading">
                    <h2>YOUR PIECES</h2>
                    <span id="cart-count" role="status">0 ITEMS</span>
                </div>

                <p id="cart-notice" class="cart-notice" role="status" hidden></p>
                <button type="button" id="cart-repair" class="remove-item" hidden>REPAIR CART</button>
                <div id="cart-items">
                    <!-- Productos generados con JavaScript -->
                </div>

            </section>

            <aside class="cart-summary">

                <p class="eyebrow">ORDER DETAILS</p>
                <h2>SUMMARY</h2>

                <div class="summary-line">
                    <span>Items</span>
                    <span id="summary-quantity">0</span>
                </div>

                <div class="summary-line">
                    <span>Subtotal</span>
                    <span id="cart-subtotal">
                        Por confirmar
                    </span>
                </div>

                <div class="summary-line">
                    <span>Shipping</span>
                    <span>Por confirmar</span>
                </div>

                <div class="summary-divider"></div>

                <div class="summary-line summary-total">
                    <span>TOTAL</span>
                    <span id="cart-total">
                        Por confirmar
                    </span>
                </div>

                <button
                    type="button"
                    class="checkout-button"
                    id="checkout-button"
                    disabled>
                    PROCEED TO CHECKOUT
                </button>

                <p class="summary-note">
                    Limited releases. All items are subject
                    to availability at checkout.
                </p>

            </aside>

        </div>

    </main>
@endsection

@section('scripts')
<script src="{{ asset('js/carrito.js') }}"></script>
<script src="{{ asset('js/cart-counter.js') }}"></script>
<script src="{{ asset('js/menu.js') }}"></script>
@endsection
