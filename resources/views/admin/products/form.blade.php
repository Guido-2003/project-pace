@extends('admin.layout')
@section('title',$product->exists?'Editar producto':'Nuevo producto')
@section('content')
@include('admin.partials.top')
<div class="admin-shell">@include('admin.partials.sidebar')
<main class="admin-main">
<a class="admin-link" href="{{ route('admin.products.index') }}">← Volver a productos</a>
<p class="eyebrow drop-kicker">CATÁLOGO / PRENDAS</p>
<h1>{{ $product->exists?'EDITAR PRODUCTO.':'NUEVO PRODUCTO.' }}</h1>
@if(session('status'))<p class="admin-success" role="status">{{ session('status') }}</p>@endif
@if($errors->any())<div class="error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form class="drop-form" method="POST" enctype="multipart/form-data" action="{{ $product->exists?route('admin.products.update',$product):route('admin.products.store') }}">
@csrf @if($product->exists) @method('PUT') @endif
<section class="panel drop-fields"><h2>Información de la prenda</h2>
<label>Drop<select name="drop_id" required><option value="">Seleccioná un drop</option>@foreach($drops as $drop)<option value="{{ $drop->id }}" @selected((string)old('drop_id',$dropId)===(string)$drop->id)>{{ $drop->name }}{{ $drop->is_published?'':' (privado)' }}</option>@endforeach</select></label>
<label>Nombre<input name="name" type="text" maxlength="255" required value="{{ old('name',$product->name) }}"></label>
<label>Descripción<textarea name="description" maxlength="12000" rows="4">{{ old('description',$product->description) }}</textarea></label>
<div class="drop-field-row"><label>Precio (Bs)<input name="price" type="number" min="0.01" max="99999999.99" step="0.01" required value="{{ old('price',$product->price) }}"><small>Solo visible en la tienda si hay stock.</small></label>
<label>Estado<select name="is_published" required><option value="0" @selected((string)old('is_published',(int)$product->is_published)==='0')>Borrador / oculto</option><option value="1" @selected((string)old('is_published',(int)$product->is_published)==='1')>Publicado</option></select><small>También debe estar publicado el drop para aparecer en la tienda.</small></label></div>
</section>
<section class="panel drop-fields"><h2>Fotografías individuales</h2><p class="muted">Independientes de la imagen editorial de ARCHIVE. JPG, PNG o WebP; máximo 5 MB por foto y 6000 × 6000 px.</p>
<div class="product-photo-grid">
@foreach(['front'=>'FRONT VIEW','back'=>'BACK VIEW'] as $view=>$label)
<div class="drop-fields"><strong>{{ $label }}</strong>
@if($url=$images->preview($product,$product->images->firstWhere('view',$view)?->path))<img class="product-admin-preview" src="{{ $url }}" alt="{{ $label }}">@else<div class="product-admin-preview product-placeholder">Sin fotografía</div>@endif
<label>{{ $product->exists?'Reemplazar imagen (opcional)':'Subir imagen' }}<input type="file" name="{{ $view }}_image" accept="image/jpeg,image/png,image/webp" @required(!$product->exists)></label>
</div>@endforeach
</div></section>
<section class="panel drop-fields"><h2>{{ $product->exists?'Inventario actual':'Tallas e inventario inicial' }}</h2>
@if($product->exists)
<p class="muted">Consulta de inventario. <a class="admin-link" href="{{ route('admin.inventory.edit',$product) }}">Editar stock y tallas →</a></p>
<div class="table-wrap"><table><thead><tr><th>Talla</th><th>Unidades</th></tr></thead><tbody>@forelse($product->variants->sortBy(fn($v)=>$v->size->sort_order) as $variant)<tr><td>{{ $variant->size->code }}</td><td>{{ $variant->stock }}</td></tr>@empty<tr><td colspan="2">Sin variantes.</td></tr>@endforelse</tbody></table></div>
@else
<p class="muted">Marcá únicamente las tallas de esta prenda y escribí su cantidad inicial. Las tallas sin marcar no se registran.</p>
@foreach($sizes as $size)<div class="product-size-row"><label class="drop-check"><input type="checkbox" name="size_ids[]" value="{{ $size->id }}" @checked(in_array((string)$size->id,array_map('strval',(array)old('size_ids',[])),true))> {{ $size->code }}</label><label>Unidades<input type="number" name="stock[{{ $size->id }}]" min="0" max="1000000" step="1" value="{{ old('stock.'.$size->id,0) }}"></label></div>@endforeach
@endif
</section>
<div class="drop-actions"><button class="primary" type="submit">{{ $product->exists?'Guardar cambios':'Crear producto' }}</button><a class="admin-link" href="{{ route('admin.products.index') }}">Cancelar</a></div>
</form>
@if($product->exists)<p class="muted">Para conservar la historia del catálogo no se permite eliminar prendas definitivamente. Podés ocultarlas sin perder sus datos.</p>@endif
</main></div>
@endsection
