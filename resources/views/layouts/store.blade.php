<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('description', 'Project Pace — prendas deportivas de lanzamientos limitados.')">
    <title>@yield('title', 'Project Pace | Performance Wear')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/02_project_pace_isotipo_circular.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @yield('styles')
</head>
<body>
    @if(request()->routeIs('checkout'))
        @include('partials.checkout-header')
    @else
        @include('partials.header')
    @endif

    @yield('content')

    @unless(request()->routeIs('checkout'))
        @include('partials.footer')
    @endunless

    <script>
        window.projectPaceCatalog = {{ \Illuminate\Support\Js::from($publicCatalog) }};
    </script>
    <script src="{{ asset('js/main.js') }}?v=db1"></script>
    <script src="{{ asset('js/cart-store.js') }}"></script>
    @yield('scripts')
</body>
</html>
