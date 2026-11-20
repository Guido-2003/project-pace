@extends('admin.layout')
@section('title','Tallas')
@section('content')
@include('admin.partials.top')
<div class="admin-shell">@include('admin.partials.sidebar')
<main class="admin-main"><a class="admin-link" href="{{ route('admin.inventory.index') }}">← Volver a inventario</a>
<p class="eyebrow drop-kicker">CATÁLOGO / TALLAS</p><h1>TALLAS.</h1><p class="muted">Incorporá nuevas tallas al catálogo. Crearlas no modifica ninguna prenda existente.</p>
@if(session('status'))<p class="admin-success" role="status">{{ session('status') }}</p>@endif
@if($errors->any())<div class="error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form class="panel drop-form drop-fields" method="POST" action="{{ route('admin.inventory.sizes.store') }}">@csrf<h2>Nueva talla</h2><div class="drop-field-row"><label>Código<input name="code" maxlength="20" required placeholder="XXL" value="{{ old('code') }}"><small>Comienza con una letra; letras, números y guiones. Se guarda en mayúsculas.</small></label><label>Orden de visualización<input name="sort_order" type="number" min="0" max="65535" step="1" required value="{{ old('sort_order',min(65535,($sizes->max('sort_order') ?? 0)+1)) }}"><small>Usá 0 para ubicar una talla antes de S o un número mayor para ubicarla al final.</small></label></div><div class="drop-actions"><button class="primary" type="submit">Crear talla</button></div></form>
<section class="panel"><div class="panel-heading"><h2>Tallas registradas</h2><span class="muted">{{ $sizes->count() }} tallas</span></div><div class="table-wrap"><table><thead><tr><th>Código</th><th>Orden</th><th>Prendas asociadas</th></tr></thead><tbody>@foreach($sizes as $size)<tr><td><strong>{{ $size->code }}</strong></td><td>{{ $size->sort_order }}</td><td>{{ $size->variants_count }}</td></tr>@endforeach</tbody></table></div><p class="muted inventory-note">No se eliminan tallas ni variantes para preservar los datos del catálogo.</p></section>
</main></div>
@endsection
