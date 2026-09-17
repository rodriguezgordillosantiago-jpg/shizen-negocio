<x-business-layout title="Pedidos Recibidos">
    @php
        $isKitchen = session('business_user.rol') === 'cocina';
    @endphp

    <div class="page-header">
        <div>
            <h1 class="page-header-title">{{ $isKitchen ? '🧑‍🍳 Vista de Cocina' : '📦 Pedidos Recibidos' }}</h1>
            <p class="page-header-sub">
                {{ $isKitchen
                    ? 'Marca los platos preparados para que el negocio los entregue al repartidor.'
                    : $orders->count() . ' pedidos registrados en tu negocio. Haz clic en un pedido para ingresar el código del repartidor.' }}
            </p>
        </div>
        @if (!$isKitchen)
        <div class="search-wrap" style="max-width:300px">
            <i class="bx bx-search"></i>
            <input type="text" class="search-input" placeholder="Buscar pedido o cliente..." oninput="filterOrders(this.value)">
        </div>
        @endif
    </div>

    {{-- Rejilla de pedidos con el mismo estilo de las tarjetas de los platos --}}
    <div id="ordersGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(310px,1fr));gap:20px;width:100%">
        @forelse ($orders as $order)
            @php
                $stKey = strtolower(trim($order->estado));
                $badgeClass = $stKey === 'entregado' ? 'badge-green' : ($stKey === 'preparado' || $stKey === 'en camino' ? 'badge-blue' : 'badge-orange');
                $cliente = trim(($order->cliente_nombre ?? '') . ' ' . ($order->cliente_apellido ?? '')) ?: 'Cliente #' . $order->id_pedido;
                $firstItem = $order->items->first();
                $imgUrl = !empty($firstItem->imagen_url) && !str_contains($firstItem->imagen_url, '../Imagenes_prueba')
                    ? $firstItem->imagen_url
                    : 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400';
            @endphp
            <div class="card order-dish-card"
                 data-search="{{ strtolower(htmlspecialchars($cliente . ' ' . $order->id_pedido . ' ' . $order->direccion_entrega, ENT_QUOTES, 'UTF-8')) }}"
                 onclick='openOrderModal(@json($order))'
                 style="cursor:pointer;display:flex;flex-direction:column;border-radius:16px;overflow:hidden;transition:transform 0.15s ease, box-shadow 0.15s ease;border:1px solid #e5e7eb"
                 onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 25px rgba(0,0,0,0.08)'"
                 onmouseout="this.style.transform='none';this.style.boxShadow='none'">
                
                <div style="position:relative;width:100%;height:150px;background:#f3f4f6;overflow:hidden">
                    <img src="{{ $imgUrl }}" alt="Pedido #{{ $order->id_pedido }}" style="width:100%;height:100%;object-fit:cover" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400'">
                    <div style="position:absolute;top:10px;left:10px;background:rgba(0,0,0,0.65);color:#fff;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:800;backdrop-filter:blur(4px)">
                        #{{ $order->id_pedido }}
                    </div>
                    <div style="position:absolute;top:10px;right:10px">
                        <span class="badge {{ $badgeClass }}" style="font-size:11px;padding:5px 10px;font-weight:700">{{ $order->estado }}</span>
                    </div>
                </div>

                <div style="padding:16px;display:flex;flex-direction:column;flex:1">
                    <div style="font-size:15px;font-weight:800;color:#111827;margin-bottom:2px">{{ $cliente }}</div>
                    <div style="font-size:12px;color:#6b7280;margin-bottom:10px;display:flex;align-items:center;gap:4px">
                        <i class="bx bx-map-pin" style="color:#ef4444"></i> {{ $order->direccion_entrega }}
                    </div>

                    <div style="background:#f9fafb;border-radius:10px;padding:10px;margin-bottom:12px;font-size:12px;color:#374151">
                        @foreach ($order->items->take(2) as $item)
                            <div style="display:flex;justify-content:space-between;margin:3px 0">
                                <span>{{ $item->nombre }}</span><strong>x{{ $item->cantidad }}</strong>
                            </div>
                        @endforeach
                        @if ($order->items->count() > 2)
                            <div style="font-size:11px;color:#6b7280;margin-top:4px">+ {{ $order->items->count() - 2 }} plato(s) más</div>
                        @endif
                    </div>

                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;padding-top:10px;border-top:1px dashed #e5e7eb">
                        <div>
                            <div style="font-size:10px;color:#6b7280;text-transform:uppercase;font-weight:700">Total (COP)</div>
                            <div style="font-size:18px;font-weight:900;color:#059669">$ {{ number_format($order->total, 0, ',', '.') }}</div>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" style="font-weight:700;padding:8px 12px;border-radius:8px;justify-content:center;text-align:center;display:inline-flex;align-items:center">
                            {{ $isKitchen ? '🧑‍🍳 Cocina' : '🔑 Código Repartidor' }}
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="card empty-state" style="grid-column:1/-1;padding:40px;text-align:center;color:#9ca3af">No hay pedidos registrados para tu negocio.</div>
        @endforelse
    </div>

    {{-- MODAL DE GESTIÓN DE PEDIDO --}}
    <div id="orderModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.6);z-index:9999;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)">
        <div class="card" style="width:100%;max-width:500px;max-height:90vh;overflow-y:auto;border-radius:20px;box-shadow:0 20px 40px rgba(0,0,0,0.25)">
            <div class="card-header" style="background:#f9fafb;border-bottom:1px solid #e5e7eb;padding:16px 20px">
                <div class="card-title" id="mOrderTitle" style="font-size:18px;font-weight:800;color:#111827">Gestión de Pedido</div>
                <button type="button" onclick="closeOrderModal()" class="btn btn-secondary btn-sm" style="padding:4px 10px;border-radius:8px;font-weight:700">✕</button>
            </div>
            <div class="card-body" style="padding:20px">
                
                <div style="background:#f9fafb;padding:14px;border-radius:12px;border:1px solid #e5e7eb;margin-bottom:16px">
                    <div style="font-size:14px;font-weight:800;color:#111827" id="mCliente"></div>
                    <div style="font-size:12px;color:#4b5563;margin-top:4px" id="mDireccion"></div>
                    <div style="font-size:12px;color:#6b7280;margin-top:2px" id="mFecha"></div>
                </div>

                <div style="margin-bottom:16px">
                    <div style="font-size:11px;font-weight:800;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px">Platos ordenados</div>
                    <div id="mItemsList" style="display:flex;flex-direction:column;gap:6px"></div>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 14px;background:#ecfdf5;border-radius:10px;border:1px solid #a7f3d0;margin-bottom:20px">
                    <span style="font-size:13px;font-weight:700;color:#065f46">Total (COP):</span>
                    <span style="font-size:20px;font-weight:900;color:#059669" id="mTotal"></span>
                </div>

                <div id="mActionArea"></div>

            </div>
        </div>
    </div>

    <script>
    const isKitchenRole = @json($isKitchen);

    function openOrderModal(order) {
        document.getElementById('mOrderTitle').textContent = 'Pedido #' + order.id_pedido + ' — Estado: ' + order.estado;
        const clienteNombre = (order.cliente_nombre || '') + ' ' + (order.cliente_apellido || '');
        document.getElementById('mCliente').innerHTML = '👤 ' + (clienteNombre.trim() || ('Cliente #' + order.id_pedido));
        document.getElementById('mDireccion').innerHTML = '📍 ' + (order.direccion_entrega || 'Sin dirección');
        document.getElementById('mFecha').innerHTML = '🕒 Fecha: ' + (order.fecha_creacion || '');
        document.getElementById('mTotal').textContent = '$ ' + Number(order.total).toLocaleString('es-CO') + ' COP';

        let itemsHtml = '';
        (order.items || []).forEach(it => {
            itemsHtml += `<div style="display:flex;justify-content:space-between;padding:8px 10px;background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;font-size:13px">
                <span><strong>${it.cantidad}x</strong> ${it.nombre || 'Plato'}</span>
                <strong style="color:#059669">$ ${Number(it.valor * it.cantidad).toLocaleString('es-CO')}</strong>
            </div>`;
        });
        document.getElementById('mItemsList').innerHTML = itemsHtml || '<div style="color:#9ca3af;font-size:12px">Sin ítems</div>';

        const st = (order.estado || '').toLowerCase();
        const area = document.getElementById('mActionArea');
        const actionUrl = '{{ route("business.orders.status", ":id") }}'.replace(':id', order.id_pedido);

        if (st === 'entregado') {
            area.innerHTML = `
                <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#059669;padding:12px;border-radius:10px;font-weight:800;text-align:center">
                    ✅ Pedido Entregado al Repartidor
                </div>`;
        } else if (st === 'cancelado') {
            area.innerHTML = `
                <div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:12px;border-radius:10px;font-weight:800;text-align:center">
                    ❌ Pedido Cancelado
                </div>`;
        } else if (isKitchenRole) {
            if (st === 'preparado' || st === 'en camino') {
                area.innerHTML = `
                    <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#047857;padding:12px;border-radius:10px;font-weight:800;text-align:center">
                        🧑‍🍳 Plato preparado por cocina. Esperando entrega por el negocio.
                    </div>`;
            } else {
                area.innerHTML = `
                    <form method="post" action="${actionUrl}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="action" value="mark_prepared">
                        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;text-align:center;display:flex;align-items:center;font-weight:800;padding:12px;font-size:15px;border-radius:10px">
                            🧑‍🍳 Marcar Plato Preparado
                        </button>
                    </form>`;
            }
        } else {
            area.innerHTML = `
                <div style="background:#ecfdf5;border:1.5px solid #a7f3d0;border-radius:12px;padding:14px">
                    <div style="font-size:13px;color:#047857;font-weight:800;margin-bottom:8px">
                        🛵 Ingresa el Código proporcionado por el Repartidor:
                    </div>
                    <form method="post" action="${actionUrl}" style="display:flex;flex-direction:column;gap:10px">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="action" value="deliver_with_code">
                        <input type="text" name="codigo_repartidor" class="form-control" placeholder="Código repartidor" required maxlength="10"
                               style="font-weight:900;letter-spacing:3px;font-size:18px;text-align:center;padding:10px;border:2px solid #059669;border-radius:10px">
                        <button type="submit" class="btn btn-primary" style="font-weight:800;padding:12px;font-size:15px;width:100%;justify-content:center;text-align:center;display:flex;align-items:center">
                            🛵 Validar Código y Entregar Pedido
                        </button>
                    </form>
                </div>`;
        }

        document.getElementById('orderModal').style.display = 'flex';
    }

    function closeOrderModal() {
        document.getElementById('orderModal').style.display = 'none';
    }

    function filterOrders(q) {
        const term = q.toLowerCase();
        document.querySelectorAll('.order-dish-card').forEach(card => {
            card.style.display = card.dataset.search.includes(term) ? '' : 'none';
        });
    }
    </script>
</x-business-layout>
