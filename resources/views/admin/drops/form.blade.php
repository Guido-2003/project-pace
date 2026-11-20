@extends('admin.layout')
@section('title', $drop->exists ? 'Editar drop' : 'Nuevo drop')
@section('content')
@include('admin.partials.top')
<div class="admin-shell">
    @include('admin.partials.sidebar')
    <main class="admin-main">
        <a class="admin-link" href="{{ route('admin.drops.index') }}">← Volver a drops</a>
        <p class="eyebrow drop-kicker">CATÁLOGO / COLECCIONES</p>
        <h1>{{ $drop->exists ? 'EDITAR DROP.' : 'NUEVO DROP.' }}</h1>
        @if(session('status'))<p class="admin-success" role="status">{{ session('status') }}</p>@endif
        @if($errors->any())<div class="error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form class="drop-form" method="POST" enctype="multipart/form-data" action="{{ $drop->exists ? route('admin.drops.update', $drop) : route('admin.drops.store') }}">
            @csrf
            @if($drop->exists) @method('PUT') @endif
            <section class="panel drop-fields">
                <h2>Información del lanzamiento</h2>
                <div class="drop-field-row">
                    <label>Número de drop
                        <input type="number" @if($drop->exists) readonly @else name="number" required @endif min="1" max="9999" value="{{ $drop->exists ? $drop->number : old('number', $nextNumber) }}">
                        <small>El número identifica el lanzamiento y no cambia después de crearlo.</small>
                    </label>
                    <label>Nombre<input type="text" name="name" maxlength="120" required value="{{ old('name', $drop->name) }}" placeholder="Drop 09"></label>
                </div>
                <label>Estado de publicación
                    <select name="is_published" required>
                        <option value="0" @selected((string) old('is_published', (int) $drop->is_published) === '0')>Borrador privado</option>
                        <option value="1" @selected((string) old('is_published', (int) $drop->is_published) === '1')>Publicado</option>
                    </select>
                    <small>Ocultar un drop oculta también todas sus prendas. Un drop publicado sin prendas públicas aparece en ARCHIVE, pero no reemplaza LATEST DROP en HOME.</small>
                </label>
            </section>
            <section class="panel drop-fields">
                <h2>Contenido de ARCHIVE</h2>
                <p class="muted">Si dejás el contenido vacío, la tienda conserva las historias y fotografías provisionales disponibles.</p>
                <label>Título editorial<input type="text" name="archive_title" maxlength="200" value="{{ old('archive_title', $drop->archive_title) }}" placeholder="Título de la historia"></label>
                <label>Historia o descripción<textarea name="archive_description" rows="7" maxlength="12000" placeholder="Historia oficial del lanzamiento...">{{ old('archive_description', $drop->archive_description) }}</textarea></label>
                @if($imageUrl)
                    <img class="drop-preview" src="{{ $imageUrl }}" alt="{{ $drop->archive_image_alt ?: 'Fotografía actual de Archive' }}">
                @endif
                <label>{{ $drop->archive_image ? 'Reemplazar fotografía' : 'Subir fotografía' }}<input type="file" name="archive_image" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG o WebP. Máximo 5 MB y 6000 × 6000 px. El archivo se almacena de forma privada.</small></label>
                @if($drop->archive_image)<label class="drop-check"><input type="checkbox" name="remove_image" value="1" @checked(old('remove_image'))> Quitar fotografía actual y volver a las fotografías provisionales.</label>@endif
                <label>Texto alternativo de la fotografía<input type="text" name="archive_image_alt" maxlength="255" value="{{ old('archive_image_alt', $drop->archive_image_alt) }}" placeholder="Descripción breve de la fotografía"></label>
            </section>
            <div class="drop-actions"><button class="primary" type="submit">{{ $drop->exists ? 'Guardar cambios' : 'Crear drop' }}</button><a class="admin-link" href="{{ route('admin.drops.index') }}">Cancelar</a></div>
        </form>
        @if($drop->exists)
        <section class="panel drops-panel">
            <div class="panel-heading"><h2>Prendas asociadas</h2><a class="admin-link" href="{{ route('admin.products.create', ['drop_id'=>$drop->id]) }}">+ Nueva prenda</a><span class="muted">{{ $products->count() }} prendas</span></div>
            <div class="table-wrap"><table><thead><tr><th>Prenda</th><th>Precio privado (Bs)</th><th>Stock</th><th>Estado público</th></tr></thead><tbody>
            @forelse($products as $product)
                <tr><td><a class="admin-link" href="{{ route('admin.products.edit', $product) }}">{{ $product->name }}</a></td><td>{{ number_format((float) $product->price, 2, '.', ',') }}</td><td>{{ (int) $product->total_stock }}</td><td>{{ $drop->is_published && $product->is_published ? 'Publicado' : 'Oculto' }}</td></tr>
            @empty<tr><td colspan="4">Todavía no hay prendas asociadas. Usá Nueva prenda para agregar productos.</td></tr>@endforelse
            </tbody></table></div>
        </section>
        @if(!$drop->is_published && $products->isEmpty())
            <section class="panel drop-danger"><h2>Eliminar borrador</h2><p class="muted">Esta acción es irreversible. Solo se permite en borradores sin prendas.</p>
                <form method="POST" action="{{ route('admin.drops.destroy', $drop) }}" class="drop-fields">@csrf @method('DELETE')
                    <label class="drop-check"><input type="checkbox" name="confirm_delete" value="1" required> Confirmo que quiero eliminar este borrador.</label>
                    <div><button class="danger-button" type="submit">Eliminar borrador</button></div>
                </form>
            </section>
        @else<p class="muted">Los drops publicados o con prendas asociadas no se pueden eliminar. Podés ocultarlos sin perder su información.</p>@endif
        @endif
    </main>
</div>
@endsection
