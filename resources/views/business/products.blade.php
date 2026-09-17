<x-business-layout title="Productos">
    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-header-title">Gestión de Productos</h1>
            <p class="page-header-sub">{{ $products->count() }} productos registrados en Pesos Colombianos (COP)</p>
        </div>
        <button class="btn btn-primary" onclick="openProductModal()"><i class="bx bx-plus"></i> Agregar producto</button>
    </div>

    @if (session('success'))
        <div class="alert alert-success"><i class="bx bx-check-circle" style="font-size:18px;flex-shrink:0"></i> {{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><i class="bx bx-error-circle" style="font-size:18px;flex-shrink:0"></i> {{ $errors->first() }}</div>
    @endif

    {{-- Toolbar de filtros --}}
    <div class="toolbar">
        <div class="search-wrap">
            <i class="bx bx-search"></i>
            <input type="text" class="search-input" placeholder="Buscar por nombre o descripción..." oninput="filterProducts(this.value)">
        </div>
        <select class="form-control" style="width:auto;padding:9px 14px" onchange="filterCategory(this.value)">
            <option value="">Todas las categorías</option>
            @foreach ($categories as $category)
                <option value="{{ $category->nombre }}">{{ $category->nombre }}</option>
            @endforeach
        </select>
        <select class="form-control" style="width:auto;padding:9px 14px" onchange="filterPromo(this.value)">
            <option value="">Todos los productos</option>
            <option value="promo">En Promoción 🔥</option>
            <option value="normal">Sin Promoción</option>
        </select>
    </div>

    {{-- Grid de productos --}}
    <div class="products-grid" id="productsGrid">
        @forelse ($products as $p)
            @php
                $hasPromo = !empty($p->on_promo);
                $rawImg = $p->imagen_url ?? '';
                if (str_contains($rawImg, 'Imagenes_prueba/')) {
                    $file = basename($rawImg);
                    $imgUrl = '/shizen-home/public/images/catalogo/Imagenes_prueba/' . $file;
                } elseif (!empty($rawImg)) {
                    $imgUrl = $rawImg;
                } else {
                    $imgUrl = 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400';
                }
            @endphp
            <div class="product-card"
                 data-name="{{ strtolower(($p->nombre ?? '') . ' ' . ($p->descripcion ?? '')) }}"
                 data-cat="{{ $p->categoria_nombre ?? '' }}"
                 data-promo="{{ $hasPromo ? 'promo' : 'normal' }}">
                
                <div class="product-card-img" style="position:relative;overflow:hidden;background:#f3f4f6;height:160px">
                    <img src="{{ $imgUrl }}"
                         alt="{{ $p->nombre ?? 'Producto' }}"
                         style="width:100%;height:100%;object-fit:cover"
                         onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400'">
                    @if ($hasPromo)
                        <span class="badge badge-orange" style="position:absolute;top:10px;right:10px;box-shadow:0 2px 6px rgba(249,115,22,.5)">
                            🔥 PROMOCIÓN
                        </span>
                    @endif
                </div>

                <div class="product-card-body">
                    <div class="product-card-name">{{ $p->nombre ?? 'Sin nombre' }}</div>
                    <div class="product-card-cat">{{ $p->categoria_nombre ?? 'Sin categoría' }}</div>
                    
                    @if (!empty($p->descripcion))
                        <div style="font-size:12px;color:#6b7280;margin-bottom:8px;line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                            {{ $p->descripcion }}
                        </div>
                    @endif

                    <div style="margin-bottom:12px;display:flex;gap:4px;flex-wrap:wrap;align-items:center">
                        @if ((int)$p->stock === 0)
                            <span class="badge badge-red">Sin stock</span>
                        @elseif ((int)$p->stock < 10)
                            <span class="badge badge-yellow">Stock bajo ({{ $p->stock }})</span>
                        @else
                            <span class="badge badge-green">Stock ({{ $p->stock }})</span>
                        @endif
                    </div>

                    <div class="product-card-footer" style="gap:4px">
                        <div>
                            @if ($hasPromo && !empty($p->precio_promocion))
                                <span class="product-price" style="color:#ea580c;font-size:15px">$ {{ number_format($p->precio_promocion, 0, ',', '.') }}</span>
                                <span style="font-size:11px;color:#9ca3af;text-decoration:line-through;display:block">$ {{ number_format($p->precio, 0, ',', '.') }}</span>
                            @else
                                <span class="product-price" style="font-size:15px">$ {{ number_format($p->precio, 0, ',', '.') }}</span>
                            @endif
                        </div>

                        <div style="display:flex;gap:4px">
                            {{-- Botón Editar --}}
                            <button class="btn btn-secondary btn-sm" style="padding:6px 8px"
                                    onclick='editProduct(@json($p))'>
                                <i class="bx bx-edit"></i>
                            </button>

                            {{-- Botón Eliminar --}}
                            <form method="post" action="{{ route('business.products.delete', $p->id_menu_item) }}" onsubmit="return confirm('¿Eliminar producto {{ $p->nombre }}?')" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="padding:6px 8px">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card" style="grid-column: 1 / -1; padding: 32px; text-align: center; color: #6b7280;">
                No hay productos registrados para este negocio.
            </div>
        @endforelse
    </div>

    {{-- ============================================================
         MODAL: Agregar / Editar Producto
    ============================================================ --}}
    <div id="productModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;padding:20px">
        <div class="card" style="width:100%;max-width:540px;max-height:90vh;overflow-y:auto">
            <div class="card-header">
                <div class="card-title" id="modalTitle">Agregar Producto</div>
                <button type="button" onclick="closeProductModal()" class="btn btn-secondary btn-sm" style="padding:4px 8px">✕</button>
            </div>
            <div class="card-body">
                <form method="post" id="productForm" action="{{ route('business.products.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="id" id="productId" value="0">

                    <div class="form-group">
                        <label class="form-label" for="prodNombre">Nombre del producto *</label>
                        <input type="text" id="prodNombre" name="nombre" class="form-control" placeholder="Ej: Hamburguesa Vegana" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="prodDescripcion">Descripción</label>
                        <textarea id="prodDescripcion" name="descripcion" class="form-control" rows="2" placeholder="Descripción del plato o ingredientes..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="prodCategory">Categoría *</label>
                        <select id="prodCategory" name="id_categoria" class="form-control" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id_categoria }}">{{ $category->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label class="form-label" for="prodPrecio">Precio Normal (COP $) *</label>
                            <input type="number" id="prodPrecio" name="precio" class="form-control" placeholder="18500" required min="1000" step="50" title="Mínimo $1.000 COP, en múltiplos de 50" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="prodStock">Stock disponible *</label>
                            <input type="number" id="prodStock" name="stock" class="form-control" value="10" required min="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Imagen del producto</label>
                        <input type="hidden" id="prodImagenUrl" name="imagen_url">
                        <div id="imgPreviewWrap" style="display:none;margin-bottom:10px;text-align:center">
                            <img id="imgPreview" src="" alt="Preview" style="max-width:100%;max-height:160px;border-radius:10px;object-fit:cover;border:2px solid #e5e7eb">
                        </div>
                        <label for="prodImagen" style="display:flex;align-items:center;gap:10px;cursor:pointer;border:2px dashed #d1d5db;border-radius:10px;padding:14px 16px;background:#f9fafb;transition:border-color .2s">
                            <i class="bx bx-image-add" style="font-size:24px;color:#6b7280"></i>
                            <div>
                                <div style="font-weight:600;color:#374151;font-size:13px">Subir imagen</div>
                                <div style="font-size:11px;color:#9ca3af">JPG, PNG, WebP o GIF · Máx. 5 MB</div>
                            </div>
                        </label>
                        <input type="file" id="prodImagen" name="imagen" accept="image/*" style="display:none" onchange="previewImg(this)">
                    </div>

                    <!-- Sección de Promoción -->
                    <div style="background:#fff7ed;padding:14px;border-radius:10px;border:1px solid #ffedd5;margin-bottom:20px">
                        <div style="font-weight:700;color:#ea580c;margin-bottom:8px">🔥 Promoción / Descuento</div>
                        <div style="font-size:12px;color:#6b7280;margin-bottom:12px">Selecciona el porcentaje de descuento. El precio promocional se calcula automáticamente.</div>
                        <div class="form-group" style="margin-bottom:0">
                            <label class="form-label" for="prodDescuentoPct" style="color:#ea580c;font-weight:600">Porcentaje de descuento</label>
                            <select id="prodDescuentoPct" name="descuento_pct" class="form-control" onchange="calcPromoPrice()">
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
                        <div id="promoPricePreview" style="display:none;margin-top:10px;padding:8px 12px;background:#ea580c;color:#fff;border-radius:8px;font-size:13px;font-weight:600"></div>
                    </div>

                    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px">
                        <button type="button" onclick="closeProductModal()" class="btn btn-secondary">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save"></i> Guardar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    const STORE_URL = '{{ route("business.products.store") }}';
    const BASE_PROD_URL = '{{ url("/productos") }}';

    function formatCOP(n) {
        return '$ ' + Math.round(n).toLocaleString('es-CO');
    }

    function calcPromoPrice() {
        const precio = parseFloat(document.getElementById('prodPrecio').value) || 0;
        const pct    = parseInt(document.getElementById('prodDescuentoPct').value, 10) || 0;
        const preview = document.getElementById('promoPricePreview');
        if (pct > 0 && precio > 0) {
            const promoPrice = Math.round(precio * (1 - pct / 100));
            preview.style.display = 'block';
            preview.textContent = '🔥 Precio promocional: ' + formatCOP(promoPrice) + ' (antes ' + formatCOP(precio) + ')';
        } else {
            preview.style.display = 'none';
            preview.textContent = '';
        }
    }

    function openProductModal() {
        document.getElementById('productForm').action = STORE_URL;
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('productId').value = '0';
        document.getElementById('modalTitle').innerText = 'Agregar Producto (Pesos Colombianos - COP)';
        document.getElementById('productForm').reset();
        document.getElementById('prodDescuentoPct').value = '0';
        document.getElementById('promoPricePreview').style.display = 'none';
        document.getElementById('prodImagenUrl').value = '';
        document.getElementById('imgPreviewWrap').style.display = 'none';
        document.getElementById('imgPreview').src = '';
        document.getElementById('productModal').style.display = 'flex';
    }

    function closeProductModal() {
        document.getElementById('productModal').style.display = 'none';
    }

    function previewImg(input) {
        const wrap = document.getElementById('imgPreviewWrap');
        const img  = document.getElementById('imgPreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                img.src = e.target.result;
                wrap.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
            document.getElementById('prodImagenUrl').value = '';
        }
    }

    function setImgPreview(url) {
        const wrap = document.getElementById('imgPreviewWrap');
        const img  = document.getElementById('imgPreview');
        if (url) {
            if (url.includes('Imagenes_prueba/')) {
                url = '/shizen-home/public/images/catalogo/Imagenes_prueba/' + url.split('Imagenes_prueba/').pop();
            }
            img.src = url;
            wrap.style.display = 'block';
        } else {
            wrap.style.display = 'none';
            img.src = '';
        }
    }

    function editProduct(p) {
        document.getElementById('productForm').action = BASE_PROD_URL + '/' + p.id_menu_item;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('productId').value = p.id_menu_item;
        document.getElementById('modalTitle').innerText = 'Editar Producto #' + p.id_menu_item;
        document.getElementById('prodNombre').value = p.nombre || '';
        document.getElementById('prodDescripcion').value = p.descripcion || '';
        document.getElementById('prodCategory').value = p.id_categoria || '';
        document.getElementById('prodPrecio').value = p.precio || '';
        document.getElementById('prodStock').value = p.stock || 0;
        document.getElementById('prodImagenUrl').value = p.imagen_url || '';
        setImgPreview(p.imagen_url || '');
        document.getElementById('prodImagen').value = '';

        const precio = parseFloat(p.precio) || 0;
        const promoPrecio = parseFloat(p.precio_promocion) || 0;
        let pctGuess = 0;
        if (p.on_promo && precio > 0 && promoPrecio > 0 && promoPrecio < precio) {
            const raw = Math.round((1 - promoPrecio / precio) * 100);
            const allowed = [10, 15, 20, 25, 30, 40, 50];
            pctGuess = allowed.reduce((prev, cur) => Math.abs(cur - raw) < Math.abs(prev - raw) ? cur : prev, 0);
        }
        document.getElementById('prodDescuentoPct').value = String(pctGuess);
        calcPromoPrice();
        document.getElementById('productModal').style.display = 'flex';
    }

    function filterProducts(q) {
        document.querySelectorAll('.product-card').forEach(c => {
            c.style.display = c.dataset.name.includes(q.toLowerCase()) ? '' : 'none';
        });
    }

    function filterCategory(cat) {
        document.querySelectorAll('.product-card').forEach(c => {
            c.style.display = (!cat || c.dataset.cat === cat) ? '' : 'none';
        });
    }

    function filterPromo(promo) {
        document.querySelectorAll('.product-card').forEach(c => {
            c.style.display = (!promo || c.dataset.promo === promo) ? '' : 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('prodPrecio').addEventListener('input', calcPromoPrice);
    });
    </script>
</x-business-layout>
