@extends('layouts.store')

@section('title', 'Product Details | Project Pace')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/product-detail.css') }}">
<link rel="stylesheet" href="{{ asset('css/footer.css') }}">
<link rel="stylesheet" href="{{ asset('css/header.css') }}">
<link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
<link rel="stylesheet" href="{{ asset('css/animations.css') }}">
@endsection

@section('content')
<main class="product-detail-page">

        <div class="detail-layout" id="product-detail">
            <!-- JavaScript cargará el producto aquí -->
        </div>

    </main>
@endsection

@section('scripts')
<script src="{{ asset('js/detalle-producto.js') }}"></script>
<script src="{{ asset('js/cart-counter.js') }}"></script>
<script src="{{ asset('js/menu.js') }}"></script>
<script src="{{ asset('js/animations.js') }}"></script>
@endsection
