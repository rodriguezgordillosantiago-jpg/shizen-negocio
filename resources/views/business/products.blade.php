<x-business-layout title="Productos">
    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-header-title">Gestión de Productos</h1>
            <p class="page-header-sub">{{ $products->count() }} productos asociados a {{ session('business_user.negocio') }}.</p>
        </div>
        <button class="btn btn-primary" onclick="openAddModal()"><i class="bx bx-plus"></i> Agregar producto</button>
    </div>

    @if (session('success'))
        <div class="alert alert-success"><i class="bx bx-check-circle" style="font-size:18px;flex-shrink:0"></i> {{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><i class="bx bx-error-circle" style="font-size:18px;flex-shrink:0"></i> {{ $errors->first() }}</div>
    @endif

    {{-- Products table --}}
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Promoción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td><strong>{{ $product->nombre }}</strong><br><small>{{ $product->descripcion }}</small></td>
                        <td>{{ $product->categoria_nombre ?? 'Sin categoría' }}</td>
                        <td>$ {{ number_format($product->precio, 0, ',', '.') }}</td>
                        <td>{{ $product->stock }}</td>
                        <td>
                            @if ($product->on_promo)
                                <span class="badge badge-orange">🔥 Activa</span><br>
                                <small>$ {{ number_format($product->precio_promocion ?? $product->precio, 0, ',', '.') }}</small>
                            @else
                                <span class="badge badge-gray">Inactiva</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-secondary btn-sm"
                                onclick='openEditModal(@json($product))'>
                                <i class="bx bx-edit"></i> Editar
                            </button>
                            <form method="post" action="{{ route('business.products.delete', $product->id_menu_item) }}"
                                  class="inline-form" onsubmit="return confirm('¿Eliminar este producto?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No hay productos registrados para este negocio.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============================================================
         MODAL: Agregar producto
    ============================================================ --}}
    <div id="addModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;padding:20px">
        <div class="card" style="width:100%;max-width:560px;max-height:90vh;overflow-y:auto">
            <div class="card-header">
                <div class="card-title">Agregar producto</div>
                <button type="button" onclick="document.getElementById('addModal').style.display='none'" class="btn btn-secondary btn-sm" style="padding:4px 8px">✕</button>
            </div>
            <div class="card-body">
                <form method="post" action="{{ route('business.products.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Nombre *</label>
                        <input class="form-control" name="nombre" placeholder="Ej: Hamburguesa Vegana" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="2" placeholder="Descripción del plato..."></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Categoría</label>
                        <select class="form-control" name="id_categoria">
                            <option value="">Sin categoría</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id_categoria }}">{{ $category->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label class="form-label">Precio (COP $) *</label>
                            <input class="form-control" id="addPrecio" name="precio" type="number" min="1000" step="50" placeholder="18500" required title="Mínimo $1.000 COP, en múltiplos de 50" oninput="this.value=this.value.replace(/[^0-9]/g,'');calcAdd()">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Stock *</label>
                            <input class="form-control" name="stock" type="number" min="0" placeholder="10" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Imagen del producto</label>
                        <div id="addImgWrap" style="display:none;margin-bottom:10px;text-align:center">
                            <img id="addImgPreview" src="" alt="" style="max-width:100%;max-height:140px;border-radius:10px;object-fit:cover;border:2px solid #e5e7eb">
                        </div>
                        <label for="addImagen" style="display:flex;align-items:center;gap:10px;cursor:pointer;border:2px dashed #d1d5db;border-radius:10px;padding:14px 16px;background:#f9fafb">
                            <i class="bx bx-image-add" style="font-size:24px;color:#6b7280"></i>
                            <div>
                                <div style="font-weight:600;color:#374151;font-size:13px">Subir imagen</div>
                                <div style="font-size:11px;color:#9ca3af">JPG, PNG, WebP o GIF · Máx. 5 MB</div>
                            </div>
                        </label>
                        <input type="file" id="addImagen" name="imagen" accept="image/*" style="display:none" onchange="previewImg(this,'addImgPreview','addImgWrap')">
                    </div>
                    {{-- Descuento --}}
                    <div style="background:#fff7ed;padding:14px;border-radius:10px;border:1px solid #ffedd5;margin-bottom:20px">
                        <div style="font-weight:700;color:#ea580c;margin-bottom:6px">🔥 Promoción / Descuento</div>
                        <div style="font-size:12px;color:#6b7280;margin-bottom:10px">El precio promocional se calcula automáticamente.</div>
                        <div class="form-group" style="margin-bottom:0">
                            <label class="form-label" style="color:#ea580c;font-weight:600">Porcentaje de descuento</label>
                            <select class="form-control" name="descuento_pct" id="addDescuento" onchange="calcAdd()">
                                <option value="0">Sin descuento (precio normal)</option>
                                <option value="10">10% de descuento</option>
                                <option value="15">15% de descuento</option>
                                <option value="20">20% de descuento</option>
                                <option value="25">25% de descuento</option>
                                <option value="30">30% de descuento</option>
                                <option value="40">40% de descuento</option>
                                <option value="50">50% de descuento</option>
                            </select>
                        </div>
                        <div id="addPreview" style="display:none;margin-top:10px;padding:8px 12px;background:#ea580c;color:#fff;border-radius:8px;font-size:13px;font-weight:600"></div>
                    </div>
                    <div style="display:flex;justify-content:flex-end;gap:10px">
                        <button type="button" onclick="document.getElementById('addModal').style.display='none'" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============================================================
         MODAL: Editar producto
    ============================================================ --}}
    <div id="editModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;padding:20px">
        <div class="card" style="width:100%;max-width:560px;max-height:90vh;overflow-y:auto">
            <div class="card-header">
                <div class="card-title" id="editModalTitle">Editar producto</div>
                <button type="button" onclick="document.getElementById('editModal').style.display='none'" class="btn btn-secondary btn-sm" style="padding:4px 8px">✕</button>
            </div>
            <div class="card-body">
                <form method="post" id="editForm" action="" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="form-group">
                        <label class="form-label">Nombre *</label>
                        <input class="form-control" id="editNombre" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" id="editDescripcion" name="descripcion" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Categoría</label>
                        <select class="form-control" id="editCategoria" name="id_categoria">
                            <option value="">Sin categoría</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id_categoria }}">{{ $category->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label class="form-label">Precio (COP $) *</label>
                            <input class="form-control" id="editPrecio" name="precio" type="number" min="1000" step="50" required title="Mínimo $1.000 COP, en múltiplos de 50" oninput="this.value=this.value.replace(/[^0-9]/g,'');calcEdit()">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Stock *</label>
                            <input class="form-control" id="editStock" name="stock" type="number" min="0" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Imagen del producto</label>
                        <input type="hidden" id="editImagenUrl" name="imagen_url">
                        <div id="editImgWrap" style="display:none;margin-bottom:10px;text-align:center">
                            <img id="editImgPreview" src="" alt="" style="max-width:100%;max-height:140px;border-radius:10px;object-fit:cover;border:2px solid #e5e7eb">
                        </div>
                        <label for="editImagen" style="display:flex;align-items:center;gap:10px;cursor:pointer;border:2px dashed #d1d5db;border-radius:10px;padding:14px 16px;background:#f9fafb">
                            <i class="bx bx-image-add" style="font-size:24px;color:#6b7280"></i>
                            <div>
                                <div style="font-weight:600;color:#374151;font-size:13px">Subir imagen</div>
                                <div style="font-size:11px;color:#9ca3af">JPG, PNG, WebP o GIF · Máx. 5 MB</div>
                            </div>
                        </label>
                        <input type="file" id="editImagen" name="imagen" accept="image/*" style="display:none" onchange="previewImg(this,'editImgPreview','editImgWrap')">
                    </div>
                    {{-- Descuento --}}
                    <div style="background:#fff7ed;padding:14px;border-radius:10px;border:1px solid #ffedd5;margin-bottom:20px">
                        <div style="font-weight:700;color:#ea580c;margin-bottom:6px">🔥 Promoción / Descuento</div>
                        <div style="font-size:12px;color:#6b7280;margin-bottom:10px">El precio promocional se calcula automáticamente.</div>
                        <div class="form-group" style="margin-bottom:0">
                            <label class="form-label" style="color:#ea580c;font-weight:600">Porcentaje de descuento</label>
                            <select class="form-control" id="editDescuento" name="descuento_pct" onchange="calcEdit()">
                                <option value="0">Sin descuento (precio normal)</option>
                                <option value="10">10% de descuento</option>
                                <option value="15">15% de descuento</option>
                                <option value="20">20% de descuento</option>
                                <option value="25">25% de descuento</option>
                                <option value="30">30% de descuento</option>
                                <option value="40">40% de descuento</option>
                                <option value="50">50% de descuento</option>
                            </select>
                        </div>
                        <div id="editPreview" style="display:none;margin-top:10px;padding:8px 12px;background:#ea580c;color:#fff;border-radius:8px;font-size:13px;font-weight:600"></div>
                    </div>
                    <div style="display:flex;justify-content:flex-end;gap:10px">
                        <button type="button" onclick="document.getElementById('editModal').style.display='none'" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="bx bx-save"></i> Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    const BASE_URL = '{{ url("/productos") }}';

    function fmtCOP(n) {
        return '$ ' + Math.round(n).toLocaleString('es-CO');
    }

    function nearestPct(raw) {
        const allowed = [10, 15, 20, 25, 30, 40, 50];
        return allowed.reduce((p, c) => Math.abs(c - raw) < Math.abs(p - raw) ? c : p, 0);
    }

    // ---- Agregar modal ----
    function openAddModal() {
        document.getElementById('addModal').style.display = 'flex';
    }
    function calcAdd() {
        const precio = parseFloat(document.getElementById('addPrecio').value) || 0;
        const pct    = parseInt(document.getElementById('addDescuento').value, 10) || 0;
        const prev   = document.getElementById('addPreview');
        if (pct > 0 && precio > 0) {
            prev.style.display = 'block';
            prev.textContent = '🔥 Precio promocional: ' + fmtCOP(precio * (1 - pct / 100)) + ' (antes ' + fmtCOP(precio) + ')';
        } else {
            prev.style.display = 'none';
        }
    }

    // ---- Shared file preview ----
    function previewImg(input, imgId, wrapId) {
        const wrap = document.getElementById(wrapId);
        const img  = document.getElementById(imgId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => { img.src = e.target.result; wrap.style.display = 'block'; };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function resolveImgUrl(url) {
        if (!url) return '';
        if (url.includes('Imagenes_prueba/')) {
            return '/shizen-home/public/images/catalogo/Imagenes_prueba/' + url.split('Imagenes_prueba/').pop();
        }
        return url;
    }

    // ---- Editar modal ----
    function openEditModal(p) {
        document.getElementById('editModalTitle').textContent = 'Editar producto #' + p.id_menu_item;
        document.getElementById('editForm').action = BASE_URL + '/' + p.id_menu_item;
        document.getElementById('editNombre').value      = p.nombre || '';
        document.getElementById('editDescripcion').value = p.descripcion || '';
        document.getElementById('editCategoria').value   = p.id_categoria || '';
        document.getElementById('editPrecio').value      = p.precio || '';
        document.getElementById('editStock').value       = p.stock || 0;
        document.getElementById('editImagenUrl').value   = p.imagen_url || '';
        // Limpiar file input
        document.getElementById('editImagen').value = '';
        // Mostrar imagen actual
        const resolvedUrl = resolveImgUrl(p.imagen_url || '');
        const wrap = document.getElementById('editImgWrap');
        const img  = document.getElementById('editImgPreview');
        if (resolvedUrl) { img.src = resolvedUrl; wrap.style.display = 'block'; }
        else { wrap.style.display = 'none'; img.src = ''; }

        // Inferir descuento
        const precio = parseFloat(p.precio) || 0;
        const promo  = parseFloat(p.precio_promocion) || 0;
        let pct = 0;
        if (p.on_promo && precio > 0 && promo > 0 && promo < precio) {
            pct = nearestPct(Math.round((1 - promo / precio) * 100));
        }
        document.getElementById('editDescuento').value = String(pct);
        calcEdit();
        document.getElementById('editModal').style.display = 'flex';
    }
    function calcEdit() {
        const precio = parseFloat(document.getElementById('editPrecio').value) || 0;
        const pct    = parseInt(document.getElementById('editDescuento').value, 10) || 0;
        const prev   = document.getElementById('editPreview');
        if (pct > 0 && precio > 0) {
            prev.style.display = 'block';
            prev.textContent = '🔥 Precio promocional: ' + fmtCOP(precio * (1 - pct / 100)) + ' (antes ' + fmtCOP(precio) + ')';
        } else {
            prev.style.display = 'none';
        }
    }
    </script>
</x-business-layout>
