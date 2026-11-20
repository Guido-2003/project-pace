<aside class="admin-side">
        <nav aria-label="Administración">
            <a @if(request()->routeIs('admin.dashboard')) class="active" aria-current="page" @endif href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a @if(request()->routeIs('admin.drops.*')) class="active" aria-current="page" @endif href="{{ route('admin.drops.index') }}">Drops</a>
            <a @if(request()->routeIs('admin.products.*')) class="active" aria-current="page" @endif href="{{ route('admin.products.index') }}">Productos</a>
            <a @if(request()->routeIs('admin.inventory.*')) class="active" aria-current="page" @endif href="{{ route('admin.inventory.index') }}">Inventario</a>
            <a href="{{ route('home') }}">Ver tienda ↗</a>
        </nav>
        <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="logout" type="submit">Cerrar sesión</button></form>
    </aside>
