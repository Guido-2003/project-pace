<header class="pp-header{{ request()->routeIs('home') ? ' pp-header--home' : '' }}" id="top">
        <nav class="pp-navbar" aria-label="Navegación principal">
            <button class="pp-menu-toggle" type="button" aria-controls="pp-main-menu"
                    aria-expanded="false" aria-label="Abrir menú" hidden>
                <svg class="pp-menu-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true">
                    <path d="M3 6h18M3 12h18M3 18h18"/>
                </svg>
                <svg class="pp-menu-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true">
                    <path d="m5 5 14 14M19 5 5 19"/>
                </svg>
            </button>
            <a href="{{ route('home') }}" class="pp-header-logo">
                <img src="{{ asset('assets/images/logo-completo-blanco.png') }}" alt="Project Pace">
            </a>
            <ul class="pp-nav-links" id="pp-main-menu">
                <li><a href="{{ route('home') }}">HOME</a></li>
                <li><a href="{{ route('home') }}#latest-drop">LATEST DROP</a></li>
                <li><a href="{{ route('shop') }}" @if(request()->routeIs('shop')) aria-current="page" @endif>SHOP</a></li>
                <li><a href="{{ route('archive') }}" @if(request()->routeIs('archive')) aria-current="page" @endif>ARCHIVE</a></li>
            </ul>
            <a href="{{ route('cart') }}" @if(request()->routeIs('cart')) aria-current="page" @endif class="cart-link" data-cart-link>CART</a>
        </nav>
    </header>
