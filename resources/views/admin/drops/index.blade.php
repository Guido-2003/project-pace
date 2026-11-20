@extends('admin.layout')
@section('title', 'Drops')
@section('content')
@include('admin.partials.top')
<div class="admin-shell">
    @include('admin.partials.sidebar')
    <main class="admin-main">
        <p class="eyebrow">CATÁLOGO / COLECCIONES</p>
        <div class="drops-heading"><h1>DROPS.</h1><a class="primary admin-action" href="{{ route('admin.drops.create') }}">+ Nuevo drop</a></div>
        <p class="muted">Prepará lanzamientos privados y actualizá las historias de ARCHIVE.</p>
        @if(session('status'))<p class="admin-success" role="status">{{ session('status') }}</p>@endif
        <section class="panel drops-panel">
            <div class="panel-heading"><h2>Lanzamientos registrados</h2><span class="muted">{{ $drops->count() }} drops</span></div>
            <div class="table-wrap"><table>
                <thead><tr><th>Drop</th><th>Prendas</th><th>Contenido editorial</th><th>Estado</th><th>Acción</th></tr></thead>
                <tbody>
                @forelse($drops as $drop)
                    <tr><td><strong>DROP {{ str_pad($drop->number, 2, '0', STR_PAD_LEFT) }}</strong><br>{{ $drop->name }}
                    @if($latest === $drop->id)<br><span class="eyebrow">LATEST DROP</span>@endif</td>
                    <td>{{ $drop->products_count }}</td>
                    <td>{{ $drop->archive_title && $drop->archive_description && $drop->archive_image ? 'Cargado' : 'Pendiente / parcial' }}</td>
                    <td><span class="drop-status {{ $drop->is_published ? 'published' : '' }}">{{ $drop->is_published ? 'Publicado' : 'Borrador' }}</span></td>
                    <td><a class="admin-link" href="{{ route('admin.drops.edit', $drop) }}">Editar →</a></td></tr>
                @empty<tr><td colspan="5">No hay drops registrados.</td></tr>@endforelse
                </tbody>
            </table></div>
        </section>
    </main>
</div>
@endsection
