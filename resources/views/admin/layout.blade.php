<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Administración') | Project Pace</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/images/02_project_pace_isotipo_circular.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="pace-admin">
    @yield('content')
</body>
</html>
