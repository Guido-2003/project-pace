@extends('admin.layout')
@section('title','Productos')
@section('content')
@include('admin.partials.top')
<div class="admin-shell">
@include('admin.partials.sidebar')
<main class="admin-main">
<p class="eyebrow">CATÁLOGO / PRENDAS</p>
<div class="drops-heading"><h1>PRODUCTOS.</h1><a class="primary admin-action" href="{{ route('admin.products.create') }}">+ Nuevo producto</a></div>
<p class="muted">Administrá las prendas, sus precios privados y fotografías.</p>
@if(session('status'))<p class="admin-success" role="status">{{ session('status') }}</p>@endif
@if($errors->any())<div class="error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form class="panel product-filters drop-fields" method="GET" action="{{ route('admin.products.index') }}">
<label>Buscar por nombre<input type="search" name="q" value="{{ request('q') }}" maxlength="120"></label>
<label>Drop<select name="drop_id"><option value="">Todos los drops</option>@foreach($drops as $drop)<option value="{{ $drop->id }}" @selected((string)request('drop_id')===(string)$drop->id)>{{ $drop->name }}</option>@endforeach</select></label>
<button type="submit" class="primary">Filtrar</button><a class="admin-link" href="{{ route('admin.products.index') }}">Limpiar</a>
</form>
<section class="panel drops-panel">
<div class="panel-heading"><h2>Prendas registradas</h2><span class="muted">{{ $products->total() }} productos</span></div>
<div class="table-wrap"><table><thead><tr><th>Prenda</th><th>Drop</th><th>Precio (Bs)</th><th>Stock</th><th>Estado público</th><th>Acción</th></tr></thead><tbody>
@forelse($products as $product)
<tr><td><div class="product-admin-cell">@if($url=$images->preview($product,$product->images->firstWhere('view','front')?->path))<img src="{{ $url }}" alt="" loading="lazy">@endif <strong>{{ $product->name }}</strong></div></td><td>{{ $product->drop->name }}</td><td>{{ number_format((float)$product->price,2,'.',',') }}</td><td>{{ (int)$product->total_stock }}</td><td><span class="drop-status {{ $product->is_published && $product->drop->is_published ? 'published' : '' }}">{{ !$product->is_published ? 'Borrador / oculto' : (!$product->drop->is_published ? 'Drop privado' : ((int)$product->total_stock > 0 ? 'Disponible' : 'Sold out')) }}</span></td><td><a class="admin-link" href="{{ route('admin.products.edit',$product) }}">Editar →</a><br><a class="admin-link" href="{{ route('admin.inventory.edit',$product) }}">Stock →</a></td></tr>
@empty<tr><td colspan="6">No se encontraron prendas.</td></tr>@endforelse
</tbody></table></div>
@if($products->hasPages())<div class="drop-actions product-pagination">@if($products->previousPageUrl())<a class="admin-link" href="{{ $products->previousPageUrl() }}">← Anterior</a>@endif<span class="muted">Página {{ $products->currentPage() }} de {{ $products->lastPage() }}</span>@if($products->nextPageUrl())<a class="admin-link" href="{{ $products->nextPageUrl() }}">Siguiente →</a>@endif</div>@endif
</section>
</main></div>
@endsection
