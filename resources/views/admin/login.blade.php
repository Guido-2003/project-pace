@extends('admin.layout')
@section('title', 'Acceso privado')
@section('content')
<main class="login-page">
    <section class="login-card" aria-labelledby="login-title">
        <img class="brand" src="{{ asset('assets/images/logo-completo-blanco.png') }}" alt="Project Pace">
        <p class="eyebrow">PROJECT PACE / ADMINISTRATION</p>
        <h1 id="login-title">Acceso privado.</h1>
        <p class="muted">Exclusivo para administradores autorizados.</p>
        @if ($errors->any())
            <div class="error" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <label for="email">Correo electrónico</label>
            <input id="email" name="email" type="email" maxlength="255" value="{{ old('email') }}" autocomplete="username" required>
            <label for="password">Contraseña</label>
            <input id="password" name="password" type="password" maxlength="72" autocomplete="current-password" required>
            <button class="primary" type="submit">INICIAR SESIÓN</button>
        </form>
        <a class="return" href="{{ route('home') }}">← Volver a la tienda</a>
    </section>
</main>
@endsection
