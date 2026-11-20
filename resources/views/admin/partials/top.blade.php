<header class="admin-top">
    <a href="{{ route('admin.dashboard') }}"><img class="brand" src="{{ asset('assets/images/logo-completo-blanco.png') }}" alt="Project Pace"></a>
    <span class="eyebrow">ADMINISTRATION</span>
    <span class="admin-user">{{ auth()->user()->name }}</span>
</header>
