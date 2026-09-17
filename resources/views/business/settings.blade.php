<x-business-layout title="Configuración">
    <div class="page-header">
        <div>
            <h1 class="page-header-title">Configuración del Sistema y Pagos</h1>
            <p class="page-header-sub">Parámetros de atención, métodos de cobro y tiempos en <strong>Pesos Colombianos (COP $)</strong></p>
        </div>
    </div>

    <form method="post" action="{{ route('business.settings.update') }}">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
            
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="bx bx-store"></i> Información del negocio</div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Nombre del negocio</label>
                        <input type="text" class="form-control" name="nombre" value="{{ old('nombre', $business->nombre) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dirección</label>
                        <input type="text" class="form-control" name="direccion" value="{{ old('direccion', $business->direccion) }}" required>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="bx bx-envelope"></i> Contacto</div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Correo del negocio</label>
                        <input type="email" class="form-control" name="gmail_negocio" value="{{ old('gmail_negocio', $business->gmail_negocio) }}" required>
                    </div>
                </div>
            </div>

        </div>

        <div style="margin-top:24px;display:flex;justify-content:flex-end">
            <button type="submit" class="btn btn-primary btn-lg" style="width:auto">
                <i class="bx bx-save"></i> Guardar configuración
            </button>
        </div>
    </form>
</x-business-layout>
