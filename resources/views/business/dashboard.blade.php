<x-business-layout title="Dashboard">
    <div class="page-header"><div><h1 class="page-header-title">Resumen del negocio</h1><p class="page-header-sub">Datos en tiempo real de la base compartida de Shizen.</p></div></div>
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon green"><i class="bx bx-package"></i></div><div><div class="stat-value">{{ number_format($productCount) }}</div><div class="stat-label">Productos en menú</div></div></div>
        <div class="stat-card"><div class="stat-icon orange"><i class="bx bx-tag"></i></div><div><div class="stat-value">{{ number_format($promoCount) }}</div><div class="stat-label">Promociones activas</div></div></div>
        <div class="stat-card"><div class="stat-icon blue"><i class="bx bx-receipt"></i></div><div><div class="stat-value">{{ number_format($orderCount) }}</div><div class="stat-label">Pedidos registrados</div></div></div>
        <div class="stat-card"><div class="stat-icon yellow"><i class="bx bx-dollar-circle"></i></div><div><div class="stat-value" style="font-size:22px">$ {{ number_format($sales, 0, ',', '.') }}</div><div class="stat-label">Ventas acumuladas (COP)</div></div></div>
    </div>
    <div class="card"><div class="card-header"><div><div class="card-title">Administración rápida</div><div class="card-subtitle">Gestiona los productos y activa promociones sin salir del panel.</div></div><a class="btn btn-primary" href="{{ route('business.products') }}">Ver productos</a></div></div>
</x-business-layout>
