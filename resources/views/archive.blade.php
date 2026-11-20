@extends('layouts.store')

@section('title', 'The Archive | Project Pace')
@section('description', 'Explora la historia de los lanzamientos limitados de Project Pace.')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/archive.css') }}">
<link rel="stylesheet" href="{{ asset('css/footer.css') }}">
<link rel="stylesheet" href="{{ asset('css/header.css') }}">
<link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
<link rel="stylesheet" href="{{ asset('css/animations.css') }}">
@endsection

@section('content')
<main class="archive-editorial">
        <section class="archive-hero" aria-labelledby="archive-title">
            <div class="archive-hero-inner">
                <p class="eyebrow">PROJECT PACE / OUR JOURNEY</p>
                <h1 id="archive-title">EVERY DROP<br>HAS A STORY<span>.</span></h1>
                <p class="archive-hero-subtitle">Different releases. One mindset.</p>
                <p class="archive-hero-description">Un recorrido por los diseños originales y los lanzamientos que forman parte de Project Pace.</p>
                <div class="archive-stats" aria-label="Resumen del archivo">
                    <div><strong id="archive-count-drops">—</strong><span>DROPS</span></div>
                    <div><strong id="archive-count-pieces">—</strong><span>ORIGINAL PIECES</span></div>
                </div>
                <a class="archive-hero-link" href="#archive-stories">EXPLORE THE JOURNEY <span aria-hidden="true">↓</span></a>
            </div>
        </section>

        <section class="archive-collections" id="archive-stories" aria-labelledby="collections-title">
            <div class="archive-section-heading">
                <div>
                    <p class="eyebrow">THE ARCHIVE / COLLECTIONS</p>
                    <h2 id="collections-title">THE RELEASES<span>.</span></h2>
                </div>
                <p>Del lanzamiento más reciente al primero. Cada prenda conserva su historia y su drop de origen.</p>
            </div>
            <nav class="archive-index" id="archive-index" aria-label="Ir a un lanzamiento"></nav>
            <div id="archive-stories-list"></div>
            <noscript>Activá JavaScript para explorar los lanzamientos.</noscript>
        </section>

        <section class="archive-end">
            <p class="eyebrow">PROJECT PACE / THE NEXT CHAPTER</p>
            <h2>THE JOURNEY<br>CONTINUES<span>.</span></h2>
            <p>Explorá el catálogo de Project Pace.</p>
            <a href="{{ route('shop') }}" class="archive-shop-link">VISIT THE SHOP &#8599;</a>
        </section>
    </main>
@endsection

@section('scripts')
<script src="{{ asset('js/archive.js') }}?v=db1"></script>
<script src="{{ asset('js/cart-counter.js') }}"></script>
<script src="{{ asset('js/menu.js') }}"></script>
<script src="{{ asset('js/animations.js') }}"></script>
@endsection
