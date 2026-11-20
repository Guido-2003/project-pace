@extends('admin.layout')
@section('title','Editar inventario')
@section('content')
@include('admin.partials.top')
<div class="admin-shell">@include('admin.partials.sidebar')
<main class="admin-main">
<a class="admin-link" href="{{ route('admin.inventory.index') }}">← Volver a inventario</a>
<p class="eyebrow drop-kicker">CATÁLOGO / CONTROL DE STOCK</p><h1>EDITAR STOCK.</h1>
<h2>{{ $product->name }}</h2><p class="muted">{{ $product->drop->name }} · <a class="admin-link" href="{{ route('admin.products.edit',$product) }}">Editar información de la prenda →</a></p>
@if(session('status'))<p class="admin-success" role="status">{{ session('status') }}</p>@endif
@if($errors->any())<div class="error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul><p>Si otra pantalla modificó el inventario, revisá los valores actuales antes de guardar.</p></div>@endif
<div class="drop-form">
<section class="panel drop-fields"><h2>Tallas registradas</h2>
<p class="muted">Introducí la cantidad absoluta disponible de cada talla. Guardar reemplaza esas cantidades; no suma unidades. Los precios y la publicación no se modifican.</p>
@if($variants->isNotEmpty())
<form class="drop-fields" method="POST" action="{{ route('admin.inventory.update',$product) }}">@csrf @method('PUT')
<div class="table-wrap"><table class="inventory-edit-table"><thead><tr><th>Talla</th><th>Stock actual</th><th>Nueva cantidad</th></tr></thead><tbody>
@foreach($variants as $variant)<tr><td><strong>{{ $variant->size->code }}</strong></td><td>{{ $variant->stock }}</td><td><input type="hidden" name="expected[{{ $variant->id }}]" value="{{ $variant->stock }}"><input aria-label="Nueva cantidad {{ $variant->size->code }}" type="number" name="stock[{{ $variant->id }}]" min="0" max="1000000" step="1" required value="{{ $variant->stock }}"></td></tr>@endforeach
</tbody></table></div><p><strong>Total actual: {{ $variants->sum('stock') }} unidades</strong></p><div class="drop-actions"><button class="primary" type="submit">Guardar cambios</button><a class="admin-link" href="{{ route('admin.inventory.index') }}">Cancelar</a></div>
</form>
@else<p class="muted">Esta prenda todavía no tiene tallas. Agregá una abajo.</p>@endif
</section>
<section class="panel drop-fields"><h2>Agregar talla a esta prenda</h2><p class="muted">Registrá una talla que todavía no pertenece a la prenda. El resto del inventario se conserva.</p>
@if($unusedSizes->isNotEmpty())
<form method="POST" class="drop-fields" action="{{ route('admin.inventory.variants.store',$product) }}">@csrf
<div class="drop-field-row"><label>Talla<select name="size_id" required><option value="">Seleccioná una talla</option>@foreach($unusedSizes as $size)<option value="{{ $size->id }}" @selected((string)old('size_id')===(string)$size->id)>{{ $size->code }}</option>@endforeach</select></label><label>Stock inicial<input type="number" name="initial_stock" min="0" max="1000000" step="1" required value="{{ old('initial_stock',0) }}"></label></div><div class="drop-actions"><button class="primary" type="submit">Agregar talla</button></div>
</form>
@else<p class="muted">Todas las tallas del catálogo ya están asociadas a esta prenda.</p>@endif
<a class="admin-link" href="{{ route('admin.inventory.sizes') }}">Gestionar tallas del catálogo →</a>
</section>
</div></main></div>
@endsection
