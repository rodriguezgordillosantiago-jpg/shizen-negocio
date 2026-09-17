<x-business-layout title="Mi Perfil y Cocina">
    <div class="page-header">
        <div>
            <h1 class="page-header-title">Mi Perfil y Gestión de Cocina</h1>
            <p class="page-header-sub">Administra tu cuenta, datos del negocio y sub-roles de cocina.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success"><i class="bx bx-check-circle" style="font-size:18px;flex-shrink:0"></i> {{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><i class="bx bx-error-circle" style="font-size:18px;flex-shrink:0"></i> {{ $errors->first() }}</div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 2fr;gap:24px;margin-bottom:24px">

        {{-- Columna izquierda: Avatar e Info del Negocio --}}
        <div style="display:flex;flex-direction:column;gap:20px">
            <div class="card">
                <div class="card-body" style="text-align:center;padding:28px 20px">
                    <div class="profile-avatar-lg" style="margin:0 auto 16px;width:72px;height:72px;border-radius:50%;background:#059669;color:#fff;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:800">
                        {{ strtoupper(substr($user->nombre ?? 'U', 0, 1)) }}
                    </div>
                    <h2 style="font-size:18px;font-weight:800;margin:0">{{ trim(($user->nombre ?? '') . ' ' . ($user->apellido ?? '')) }}</h2>
                    <p style="color:#6b7280;font-size:13px;margin:4px 0 12px">{{ $user->email }}</p>
                    <span class="badge badge-green">{{ strtolower((string)$user->rol) === 'cocina' ? 'Cocina' : 'Administrador Negocio' }}</span>
                </div>
            </div>

            {{-- Información del Negocio Actual --}}
            <div class="card">
                <div class="card-header" style="background:#f9fafb;border-bottom:1px solid #e5e7eb">
                    <div class="card-title" style="font-size:15px;display:flex;align-items:center;gap:8px">
                        <i class="bx bx-store" style="color:#059669;font-size:20px"></i> Negocio Actual
                    </div>
                </div>
                <div class="card-body" style="padding:16px 20px">
                    @if ($business)
                        <div style="font-size:16px;font-weight:800;color:#111827;margin-bottom:6px">{{ $business->nombre }}</div>
                        <div style="font-size:13px;color:#4b5563;margin-bottom:4px">
                            <i class="bx bx-map-pin" style="color:#ef4444"></i> {{ $business->direccion ?? 'Sin dirección' }}
                        </div>
                        <div style="font-size:13px;color:#4b5563">
                            <i class="bx bx-envelope" style="color:#2563eb"></i> {{ $business->gmail_negocio ?? $user->email }}
                        </div>
                        <div style="margin-top:12px;font-size:11px;color:#6b7280;background:#ecfdf5;padding:6px 10px;border-radius:6px;border:1px solid #a7f3d0">
                            <strong>ID Negocio:</strong> #{{ $business->id_negocio }}
                        </div>
                    @else
                        <p style="color:#6b7280;font-size:13px">No se encontró información del negocio.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Columna derecha: Editar Perfil + Registrar Sub-rol Cocina --}}
        <div style="display:flex;flex-direction:column;gap:24px">

            {{-- Editar Información Personal --}}
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Editar información personal</div>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('business.profile.update') }}">
                        @csrf
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                            <div class="form-group">
                                <label class="form-label" for="nombre">Nombre</label>
                                <input class="form-control" id="nombre" name="nombre" value="{{ $user->nombre }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="apellido">Apellido</label>
                                <input class="form-control" id="apellido" name="apellido" value="{{ $user->apellido }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="email">Correo electrónico</label>
                            <input class="form-control" id="email" type="email" name="email" value="{{ $user->email }}" required>
                        </div>
                        <button class="btn btn-primary" type="submit"><i class="bx bx-check"></i> Guardar cambios</button>
                    </form>
                </div>
            </div>

            {{-- Registrar Sub-Rol de Cocina --}}
            <div class="card" style="border:2px solid #bfdbfe">
                <div class="card-header" style="background:#eff6ff">
                    <div>
                        <div class="card-title" style="color:#1e40af;display:flex;align-items:center;gap:8px">
                            🧑‍🍳 Registrar Sub-Rol de Cocina
                        </div>
                        <div class="card-subtitle" style="color:#3b82f6">
                            Crea usuarios dedicados exclusivamente a la vista de cocina para tu negocio
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('business.profile.kitchen') }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Nombre del encargado de cocina *</label>
                            <input class="form-control" name="nombre" placeholder="Ej: Pedro Cocina" required>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                            <div class="form-group">
                                <label class="form-label">Correo electrónico (login cocina) *</label>
                                <input class="form-control" type="email" name="email" placeholder="cocina@minegocio.com" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Contraseña *</label>
                                <input class="form-control" type="password" name="password" placeholder="Mínimo 6 caracteres" required>
                            </div>
                        </div>
                        <button class="btn btn-primary" type="submit" style="background:#2563eb;border:none">
                            <i class="bx bx-plus-circle"></i> Registrar Sub-Rol Cocina
                        </button>
                    </form>

                    {{-- Lista de cuentas de cocina asociadas --}}
                    <div style="margin-top:24px;padding-top:16px;border-top:1px dashed #cbd5e1">
                        <div style="font-size:13px;font-weight:700;color:#1e293b;margin-bottom:10px">
                            Cuentas de cocina asociadas ({{ count($kitchenUsers) }})
                        </div>
                        @forelse ($kitchenUsers as $ku)
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;margin-bottom:6px">
                                <div>
                                    <div style="font-size:13px;font-weight:700;color:#0f172a">🧑‍🍳 {{ trim($ku->nombre . ' ' . $ku->apellido) }}</div>
                                    <div style="font-size:12px;color:#64748b">{{ $ku->email }}</div>
                                </div>
                                <span class="badge badge-blue" style="font-size:11px">Rol Cocina</span>
                            </div>
                        @empty
                            <div style="font-size:12px;color:#94a3b8">No has registrado cuentas de cocina todavía.</div>
                        @endforelse
                    </div>

                </div>
            </div>

        </div>

    </div>
</x-business-layout>
