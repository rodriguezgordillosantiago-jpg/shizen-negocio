<x-business-layout title="Configuración">
    <div class="page-header">
        <div>
            <h1 class="page-header-title">Configuración del sistema</h1>
            <p class="page-header-sub">Parámetros de operación y moneda oficial (Pesos Colombianos - COP).</p>
        </div>
    </div>
    @if (session('success'))
        <div class="alert alert-success"><i class="bx bx-check-circle" style="font-size:18px;flex-shrink:0"></i> {{ session('success') }}</div>
    @endif
    <form method="post" action="{{ route('business.settings.update') }}">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Moneda oficial del sistema</label>
                    <input class="form-control" value="Pesos Colombianos (COP - $)" disabled style="background:#f0fdf4;color:#047857;font-weight:700">
                </div>
                <div class="form-group">
                    <label class="form-label">Tiempo estimado de preparación (minutos)</label>
                    <input class="form-control" type="number" name="prepTime" value="25" min="5" max="120">
                </div>
                <div class="form-group">
                    <label class="form-label">Valor mínimo de pedido (COP $)</label>
                    <input class="form-control" type="number" name="minOrder" value="15000">
                </div>
                <button class="btn btn-primary" type="submit"><i class="bx bx-save"></i> Guardar configuración</button>
            </div>
        </div>
    </form>
</x-business-layout>
