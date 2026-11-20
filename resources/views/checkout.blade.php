@extends('layouts.store')

@section('title', 'Checkout | Project Pace')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/checkout.css') }}">
<link rel="stylesheet" href="{{ asset('css/header.css') }}">
<link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
@endsection

@section('content')
<main class="pace-checkout">
        <div class="checkout-heading">
            <p class="eyebrow">PROJECT PACE / FINAL STEP</p>
            <h1>CHECKOUT<span>.</span></h1>
            <p>Train with precision. Elevate your performance.</p>
        </div>
        <p class="checkout-demo">Modo de prueba · No se enviarán datos, pedidos ni pagos.</p>
        <div id="checkout-blocked" class="checkout-blocked" role="status" hidden>
            <h2 id="checkout-blocked-title"></h2>
            <p id="checkout-blocked-text"></p>
            <a href="{{ route('cart') }}" class="checkout-action">BACK TO CART</a>
            <a href="{{ route('shop') }}" class="checkout-secondary">CONTINUE SHOPPING</a>
        </div>
        <div class="checkout-layout" id="checkout-layout" hidden>
            <form id="checkout-form" class="checkout-form">
                <section class="checkout-section">
                    <p class="eyebrow">01 / CONTACT</p>
                    <h2>Datos de contacto</h2>
                    <label for="customer-name">Nombre completo *</label>
                    <input id="customer-name" name="customerName" autocomplete="name" required maxlength="120">
                    <label for="customer-phone">Teléfono / WhatsApp *</label>
                    <input id="customer-phone" name="phone" type="tel" autocomplete="tel" required maxlength="30" placeholder="Ej. +591 70000000">
                    <label for="customer-email">Correo electrónico (opcional)</label>
                    <input id="customer-email" name="email" type="email" autocomplete="email" maxlength="160">
                </section>
                <section class="checkout-section">
                    <p class="eyebrow">02 / DELIVERY</p>
                    <h2>Datos de entrega</h2>
                    <label for="delivery-method">Modalidad de entrega (de prueba) *</label>
                    <select id="delivery-method" name="deliveryMethod" required>
                        <option value="">Seleccioná una opción</option>
                        <option value="delivery">Envío a domicilio</option>
                        <option value="pickup">Recojo a coordinar</option>
                    </select>
                    <div id="delivery-address" hidden>
                        <label for="customer-city">Ciudad *</label>
                        <input id="customer-city" name="city" autocomplete="address-level2" maxlength="100" disabled>
                        <label for="customer-address">Dirección y referencia *</label>
                        <textarea id="customer-address" name="address" autocomplete="street-address" rows="3" maxlength="300" disabled></textarea>
                    </div>
                    <label for="customer-notes">Observaciones (opcional)</label>
                    <textarea id="customer-notes" name="notes" rows="3" maxlength="300"></textarea>
                    <p class="checkout-muted">Las modalidades, zonas y tarifas de entrega están pendientes de confirmación.</p>
                </section>
                <section class="checkout-section">
                    <p class="eyebrow">03 / PAYMENT</p>
                    <h2>Método de pago</h2>
                    <p class="checkout-muted">Por definir con Project Pace. No se solicitarán datos bancarios ni se realizará ningún cobro.</p>
                </section>
                <p class="checkout-muted">* Campos obligatorios. Utilizá datos ficticios en esta prueba.</p>
            </form>
            <aside class="checkout-summary" aria-label="Resumen del pedido">
                <p class="eyebrow">YOUR SELECTION</p>
                <h2>ORDER SUMMARY</h2>
                <div id="checkout-items"></div>
                <div class="checkout-summary-line"><span>Subtotal</span><strong id="checkout-subtotal">—</strong></div>
                <div class="checkout-summary-line"><span>Envío</span><span>Por confirmar</span></div>
                <div class="checkout-summary-line checkout-grand-total"><span>Total final</span><strong>Por confirmar</strong></div>
                <button class="checkout-action" type="submit" form="checkout-form">REVIEW ORDER</button>
                <p class="checkout-muted">La disponibilidad y el precio definitivo se validarán en el servidor en la versión final.</p>
            </aside>
        </div>
        <section id="checkout-review" class="checkout-review" hidden aria-labelledby="review-heading">
            <p class="eyebrow">ORDER PREVIEW / DEMO</p>
            <h2 id="review-heading" tabindex="-1">Revisá tu solicitud de prueba.</h2>
            <p>No se ha creado un pedido ni reservado inventario. Los datos se muestran solo en esta pantalla.</p>
            <dl id="checkout-review-data"></dl>
            <p id="checkout-review-total"></p>
            <a href="{{ route('cart') }}" class="checkout-secondary">BACK TO CART</a>
            <button type="button" class="checkout-action" id="edit-checkout">EDIT DETAILS</button>
        </section>
    </main>
@endsection

@section('scripts')
<script src="{{ asset('js/checkout.js') }}"></script>
@endsection
