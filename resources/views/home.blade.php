@extends('layouts.store')

@section('title', 'Project Pace | Performance Wear')
@section('description', 'Project Pace — prendas deportivas de lanzamientos limitados.')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/footer.css') }}">
<link rel="stylesheet" href="{{ asset('css/header.css') }}">
<link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
<link rel="stylesheet" href="{{ asset('css/animations.css') }}">
@endsection

@section('content')
<main>

        <section class="hero" id="inicio">

            <div class="hero-content">
                <p class="hero-label">PROJECT PACE / PERFORMANCE WEAR</p>

                <h1>TRAIN WITH<br>PRECISION.</h1>

                <p class="hero-description">
                    ELEVATE YOUR PERFORMANCE.
                </p>

                <a href="#latest-drop" class="hero-button">
                    EXPLORAR COLECCIÓN
                </a>
            </div>

        </section>

        <!-- ÚLTIMO LANZAMIENTO -->
        <section class="latest-drop" id="latest-drop">

            <div class="collection-heading">
                <div>
                    <p class="eyebrow">PROJECT PACE / LATEST RELEASE</p>
                    <h2>THE LATEST DROP<span>.</span></h2>
                    <p class="collection-description">
                        Limited pieces. Built with purpose.
                        Created to elevate your performance.
                    </p>
                </div>

                <a href="{{ route('shop') }}" class="collection-link">
                    SHOP ALL PIECES &#8599;
                </a>
            </div>

            <div class="product-grid" id="latest-products">
                <!-- Productos generados con JavaScript -->
            </div>

        </section>


        <!-- SELECCIÓN DE DROPS ANTERIORES -->
        <section class="shop-section" id="shop">

            <div class="collection-heading">
                <div>
                    <p class="eyebrow">STILL IN ROTATION</p>
                    <h2>PREVIOUS DROPS<span>.</span></h2>
                    <p class="collection-description">
                        Selected pieces from previous releases.
                        Once they're gone, they're gone.
                    </p>
                </div>
            </div>

            <div class="product-grid" id="available-products">
                <!-- Selección de prendas de lanzamientos anteriores -->
            </div>

        </section>


        <!-- HISTORIAL DE DROPS -->
        <section class="archive-section" id="archive">

            <div class="archive-content">
                <p class="eyebrow">THE PROJECT PACE ARCHIVE</p>

                <h2>EVERY DROP<br>HAS A STORY.</h2>

                <p>
                    A collection of past releases.
                    Different designs. Different moments.
                    One mindset.
                </p>

                <a href="{{ route('archive') }}" class="archive-note">
                    EXPLORE THE ARCHIVE &#8599;
                </a>
            </div>

        </section>


    </main>
@endsection

@section('scripts')
<script src="{{ asset('js/cart-counter.js') }}"></script>
<script src="{{ asset('js/menu.js') }}"></script>
<script src="{{ asset('js/animations.js') }}"></script>
@endsection
