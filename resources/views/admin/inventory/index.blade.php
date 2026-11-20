@extends('admin.layout')
@section('title','Inventario')
@section('content')
@include('admin.partials.top')
<div class="admin-shell">@include('admin.partials.sidebar')
<main class="admin-main">
<p class="eyebrow">CATÁLOGO / CONTROL DE STOCK</p>
<div class="drops-heading"><h1>INVENTARIO.</h1><a class="primary admin-action" href="{{ route('admin.inventory.sizes') }}">Gestionar tallas →</a></div>
<p class="muted">Consultá y actualizá las unidades disponibles de cada prenda por talla.</p>
@if(session('status'))<p class="admin-success" role="status">{{ session('status') }}</p>@endif
@if($errors->any())<div class="error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="stats inventory-stats">
@foreach(['Unidades disponibles'=>$stockCount,'Variantes registradas'=>$variantCount,'Prendas con stock'=>$availableCount,'Prendas sin stock'=>$soldOutCount] as $label=>$value)
<div class="stat"><p>{{ $label }}</p><strong>{{ $value }}</strong></div>
@endforeach
</div>
<p class="muted">Totales de inventario (incluyen borradores). La visibilidad pública depende también de la publicación del producto y del drop.</p>
<form class="panel drop-fields inventory-filters" method="GET" action="{{ route('admin.inventory.index') }}">
<label>Buscar por nombre<input name="q" type="search" maxlength="120" value="{{ request('q') }}"></label>
<label>Drop<select name="drop_id"><option value="">Todos los drops</option>@foreach($drops as $drop)<option value="{{ $drop->id }}" @selected((string)request('drop_id')===(string)$drop->id)>{{ $drop->name }}</option>@endforeach</select></label>
<label>Disponibilidad física<select name="availability"><option value="">Todas</option><option value="available" @selected(request('availability')==='available')>Con stock</option><option value="soldout" @selected(request('availability')==='soldout')>Sin stock</option></select></label>
<button class="primary" type="submit">Filtrar</button><a class="admin-link" href="{{ route('admin.inventory.index') }}">Limpiar</a>
</form>
<section class="panel drops-panel">
<div class="panel-heading"><h2>Stock por producto</h2><span class="muted">{{ $products->total() }} productos</span></div>
<p class="muted inventory-note">0 = talla registrada y agotada. — = talla no registrada para esa prenda.</p>
<div class="table-wrap"><table class="inventory-table"><thead><tr><th>Prenda</th><th>Drop</th>@foreach($sizes as $size)<th class="inventory-number">{{ $size->code }}</th>@endforeach<th class="inventory-number">Total</th><th>Estado público</th><th>Acción</th></tr></thead><tbody>
@forelse($products as $product)
@php($bySize=$product->variants->keyBy('size_id'))
<tr><td><strong>{{ $product->name }}</strong></td><td>{{ $product->drop->name }}</td>@foreach($sizes as $size)<td class="inventory-number {{ $bySize->has($size->id)?'':'muted' }}">{{ $bySize->get($size->id)?->stock ?? '—' }}</td>@endforeach<td class="inventory-number"><strong>{{ (int)$product->total_stock }}</strong></td><td><span class="drop-status {{ $product->is_published && $product->drop->is_published && (int)$product->total_stock>0?'published':'' }}">{{ !$product->is_published?'Borrador / oculto':(!$product->drop->is_published?'Drop privado':((int)$product->total_stock>0?'Disponible':'Sold out')) }}</span></td><td><a class="admin-link" href="{{ route('admin.inventory.edit',$product) }}">Editar stock →</a></td></tr>
@empty<tr><td colspan="{{ $sizes->count()+5 }}">No se encontraron prendas.</td></tr>@endforelse
</tbody></table></div>
@if($products->hasPages())<div class="drop-actions product-pagination">@if($products->previousPageUrl())<a class="admin-link" href="{{ $products->previousPageUrl() }}">← Anterior</a>@endif<span class="muted">Página {{ $products->currentPage() }} de {{ $products->lastPage() }}</span>@if($products->nextPageUrl())<a class="admin-link" href="{{ $products->nextPageUrl() }}">Siguiente →</a>@endif</div>@endif
</section>
</main></div>
@endsection
