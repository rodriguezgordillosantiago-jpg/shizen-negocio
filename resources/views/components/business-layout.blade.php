<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Panel' }} | Shizen Negocio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/business.css?v=20260913-kitchen-nav') }}">
</head>
<body class="layout{{ session('business_user.rol') === 'cocina' ? ' kitchen-layout' : '' }}">
    @if (session('business_user.rol') === 'cocina')
    <nav class="kitchen-nav">
        <a class="kitchen-brand" href="{{ route('business.orders') }}"><img src="{{ asset('img/logo.png') }}" alt="SHIZEN"></a>
        <div class="kitchen-divider"></div>
        <div class="kitchen-title"><strong>Vista de Cocina</strong><span>Cocinero: {{ session('business_user.nombre') }}</span></div>
        <form method="post" action="{{ route('logout') }}" class="kitchen-logout-form">@csrf<button class="kitchen-logout" type="submit">Cerrar Sesión</button></form>
    </nav>
    @endif
    @if (session('business_user.rol') !== 'cocina')
    <nav class="sidebar">
        <div class="sidebar-brand">
            <a href="{{ route('business.dashboard') }}" class="sidebar-logo">
                <img src="{{ asset('img/logo.png') }}" alt="Shizen Negocio">
            </a>
        </div>
        <div class="sidebar-nav">
            @if (session('business_user.rol') === 'cocina')
                <p class="sidebar-label">Área de cocina</p>
                <a class="nav-item active" href="{{ route('business.orders') }}"><i class="bx bx-dish"></i>Pedidos</a>
            @else
            <p class="sidebar-label">Menu principal</p>
            <a class="nav-item {{ request()->routeIs('business.dashboard') ? 'active' : '' }}" href="{{ route('business.dashboard') }}"><i class="bx bx-home-alt-2"></i>Dashboard</a>
            <a class="nav-item {{ request()->routeIs('business.products*') ? 'active' : '' }}" href="{{ route('business.products') }}"><i class="bx bx-package"></i>Productos</a>
            <a class="nav-item {{ request()->routeIs('business.orders*') ? 'active' : '' }}" href="{{ route('business.orders') }}"><i class="bx bx-receipt"></i>Pedidos</a>
            <a class="nav-item {{ request()->routeIs('business.clients') ? 'active' : '' }}" href="{{ route('business.clients') }}"><i class="bx bx-group"></i>Clientes</a>
            <a class="nav-item {{ request()->routeIs('business.notifications') ? 'active' : '' }}" href="{{ route('business.notifications') }}"><i class="bx bx-bell"></i>Notificaciones</a>
            <a class="nav-item {{ request()->routeIs('business.settings*') ? 'active' : '' }}" href="{{ route('business.settings') }}"><i class="bx bx-cog"></i>Configuración</a>
            <a class="nav-item {{ request()->routeIs('business.profile*') ? 'active' : '' }}" href="{{ route('business.profile') }}"><i class="bx bx-user"></i>Mi perfil</a>
            @endif
        </div>
        <div class="sidebar-footer">
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button class="nav-item nav-item-logout" type="submit"><i class="bx bx-log-out"></i>Cerrar sesión</button>
            </form>
        </div>
    </nav>
    @endif
    <div class="main">
        @if (session('business_user.rol') !== 'cocina')<header class="topbar">
            <div class="topbar-left">
                <span class="topbar-title">{{ $title ?? 'Panel' }}</span>
                <span class="topbar-sub">Bienvenido, {{ session('business_user.nombre') }}</span>
            </div>
            <div class="topbar-user">
                <div class="topbar-avatar">{{ strtoupper(substr(session('business_user.nombre'), 0, 1)) }}</div>
                <div>
                    <div class="topbar-name">{{ session('business_user.negocio') }}</div>
                    <div class="topbar-role">{{ session('business_user.rol') === 'cocina' ? 'Cocina' : 'Administrador del negocio' }}</div>
                </div>
            </div>
        </header>@endif
        <main class="content">
            @if (session('success'))
                <div class="alert alert-success"><i class="bx bx-check-circle"></i>{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger"><i class="bx bx-error-circle"></i>{{ $errors->first() }}</div>
            @endif
            {{ $slot }}
        </main>
    </div>
</body>
</html>
