<x-business-layout title="Clientes">
    <div class="page-header">
        <div>
            <h1 class="page-header-title">Clientes del Negocio</h1>
            <p class="page-header-sub">{{ $clients->count() }} clientes que han comprado en tu negocio.</p>
        </div>
    </div>
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Correo electrónico</th>
                        <th>Dirección de Entrega</th>
                        <th>Pedidos Realizados</th>
                        <th>Total Gastado (COP)</th>
                        <th>Última Compra</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($clients as $client)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                <div style="width:36px;height:36px;border-radius:50%;background:#d1fae5;color:#047857;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;flex-shrink:0">
                                    {{ strtoupper(substr($client->nombre ?? 'C', 0, 1)) }}
                                </div>
                                <strong style="color:#111827">{{ trim($client->nombre . ' ' . $client->apellido) }}</strong>
                            </div>
                        </td>
                        <td style="font-size:13px;color:#374151">{{ $client->email }}</td>
                        <td style="font-size:13px;color:#4b5563">{{ $client->direccion ?? 'Dirección en pedido' }}</td>
                        <td><strong style="font-size:14px;color:#111827">{{ $client->total_pedidos }}</strong></td>
                        <td><strong style="color:#059669;font-size:14px">$ {{ number_format($client->total_gastado, 0, ',', '.') }}</strong></td>
                        <td style="color:#6b7280;font-size:12px">{{ $client->ultima_compra ? \Carbon\Carbon::parse($client->ultima_compra)->format('d/m/Y H:i') : 'Sin fecha' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No hay clientes que hayan comprado en tu negocio aún.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-business-layout>
