@extends('admin.layout')
@section('title', 'Dashboard')
@section('content')
@include('admin.partials.top')
<div class="admin-shell">
    @include('admin.partials.sidebar')
    <main class="admin-main">
        <p class="eyebrow">PROJECT PACE / ADMIN</p>
        <h1>DASHBOARD.</h1>
        <p class="muted">Resumen del catálogo completo, incluidas las prendas no publicadas.</p>
        <section class="stats" aria-label="Indicadores">
            @foreach (['Drops' => $dropCount, 'Productos' => $productCount, 'Con stock' => $availableCount, 'Agotados' => $soldOutCount, 'Unidades en inventario' => $stockCount, 'Variantes' => $variantCount] as $label => $value)
                <article class="stat"><p>{{ $label }}</p><strong>{{ number_format($value, 0, ',', '.') }}</strong></article>
            @endforeach
        </section>
        <section class="panel" aria-labelledby="catalog-title">
            <div class="panel-heading"><div><p class="eyebrow">INFORMACIÓN PRIVADA</p><h2 id="catalog-title">Catálogo registrado</h2></div><span class="muted">Solo lectura</span></div>
            <div class="table-wrap"><table><thead><tr><th>Prenda</th><th>Drop</th><th>Precio (Bs)</th><th>Stock</th><th>Publicación</th></tr></thead><tbody>
            @forelse ($products as $product)
                <tr><td>{{ $product->name }}</td><td>{{ $product->drop->name }}</td><td>{{ number_format((float) $product->price, 2, '.', ',') }}</td><td>{{ (int) $product->total_stock }}</td><td>{{ $product->is_published && $product->drop->is_published ? 'Publicado' : 'Oculto' }}</td></tr>
            @empty
                <tr><td colspan="5">No hay productos registrados.</td></tr>
            @endforelse
            </tbody></table></div>
        </section>
    </main>
</div>
@endsection
