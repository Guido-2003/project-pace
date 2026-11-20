@extends('layouts.store')

@section('title', 'Shop All Pieces | Project Pace')
@section('description', 'Explora las prendas y lanzamientos limitados de Project Pace.')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/shop.css') }}">
<link rel="stylesheet" href="{{ asset('css/footer.css') }}">
<link rel="stylesheet" href="{{ asset('css/header.css') }}">
<link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
<link rel="stylesheet" href="{{ asset('css/animations.css') }}">
@endsection

@section('content')
<main class="shop-page">
        <section class="shop-intro">
            <p class="eyebrow">PROJECT PACE / LIMITED RELEASES</p>
            <h1>SHOP ALL PIECES<span>.</span></h1>
            <p>Every drop has its moment. Explore the collection.</p>
        </section>

        <section class="shop-catalog" aria-label="Catálogo de prendas">
            <form class="shop-filters" id="shop-filters" role="search">
                <div class="shop-filter-field shop-search-field">
                    <label for="shop-search">SEARCH PIECES</label>
                    <input id="shop-search" type="search" placeholder="Buscar una prenda..." autocomplete="off">
                </div>

                <div class="shop-filter-field">
                    <label for="shop-drop">FILTER BY DROP</label>
                    <select id="shop-drop">
                        <option value="all">ALL DROPS</option>
                    </select>
                </div>

                <div class="shop-filter-field">
                    <label for="shop-sort">SORT BY</label>
                    <select id="shop-sort">
                        <option value="newest">NEWEST DROP</option>
                        <option value="oldest">OLDEST DROP</option>
                    </select>
                </div>
            </form>

            <div class="shop-results-bar">
                <p id="shop-count" role="status" aria-live="polite">Cargando colección...</p>
                <button type="button" class="shop-clear-button" id="shop-reset">CLEAR FILTERS</button>
            </div>

            <div class="product-grid shop-grid" id="shop-products">
                <!-- Las tarjetas se generan con shop.js, usando el catálogo de main.js -->
            </div>

            <div class="shop-empty" id="shop-empty" hidden>
                <h2>NO PIECES FOUND.</h2>
                <p>Probá con otro nombre o seleccioná otro drop.</p>
                <button type="button" id="shop-empty-reset">VIEW ALL PIECES</button>
            </div>
        </section>
    </main>
@endsection

@section('scripts')
<script src="{{ asset('js/shop.js') }}"></script>
<script src="{{ asset('js/cart-counter.js') }}"></script>
<script src="{{ asset('js/menu.js') }}"></script>
<script src="{{ asset('js/animations.js') }}"></script>
@endsection
