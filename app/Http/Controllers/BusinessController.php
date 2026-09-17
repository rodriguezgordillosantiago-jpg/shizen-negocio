<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class BusinessController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('business_user')) {
            return $request->session()->get('business_user.rol') === 'cocina'
                ? redirect()->route('business.orders')
                : redirect()->route('business.dashboard');
        }

        return view('business.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = DB::table('usuario')
            ->where('email', strtolower(trim($credentials['email'])))
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password_hash)) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Correo o contraseña incorrectos.',
            ]);
        }

        $business = DB::table('negocios')
            ->where('id_usuario', $user->id_usuario)
            ->first();

        if (! $business && strtolower((string) $user->rol) === 'cocina' && $user->id_cocina_negocio_asociado) {
            $business = DB::table('negocios')
                ->where('id_negocio', $user->id_cocina_negocio_asociado)
                ->first();
        }
        if (! $business) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Esta cuenta no está asociada a un negocio.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('business_user', [
            'id_usuario' => (int) $user->id_usuario,
            'nombre' => trim($user->nombre . ' ' . $user->apellido),
            'email' => $user->email,
            'rol' => strtolower((string) $user->rol),
            'id_negocio' => (int) $business->id_negocio,
            'negocio' => $business->nombre,
        ]);

        return strtolower((string) $user->rol) === 'cocina'
            ? redirect()->route('business.orders')
            : redirect()->route('business.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('business_user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function dashboard(Request $request): View
    {
        $businessId = $this->businessId($request);
        $products = DB::table('menu_items')->where('id_negocio', $businessId);

        return view('business.dashboard', [
            'productCount' => (clone $products)->count(),
            'promoCount' => (clone $products)->where('on_promo', 1)->count(),
            'orderCount' => DB::table('pedido as p')
                ->join('detalle_pedido as d', 'd.id_pedido', '=', 'p.id_pedido')
                ->join('menu_items as m', 'm.id_menu_item', '=', 'd.id_menu_item')
                ->where('m.id_negocio', $businessId)
                ->distinct('p.id_pedido')
                ->count('p.id_pedido'),
            'sales' => (float) DB::table('detalle_pedido as d')
                ->join('menu_items as m', 'm.id_menu_item', '=', 'd.id_menu_item')
                ->where('m.id_negocio', $businessId)
                ->selectRaw('COALESCE(SUM(d.valor * d.cantidad), 0) as total')
                ->value('total'),
        ]);
    }

    public function products(Request $request): View
    {
        $businessId = $this->businessId($request);

        return view('business.products', [
            'products' => DB::table('menu_items as m')
                ->leftJoin('categorias as c', 'c.id_categoria', '=', 'm.id_categoria')
                ->where('m.id_negocio', $businessId)
                ->select('m.*', 'c.nombre as categoria_nombre')
                ->orderByDesc('m.id_menu_item')
                ->get(),
            'categories' => DB::table('categorias')->orderBy('nombre')->get(),
        ]);
    }

    public function orders(Request $request): View
    {
        $businessId = $this->businessId($request);
        $orders = DB::table('pedido as p')
            ->leftJoin('usuario as u', 'u.id_usuario', '=', 'p.id_usuario')
            ->leftJoin('compra as c', 'c.id_pedido', '=', 'p.id_pedido')
            ->leftJoin('entrega as e', 'e.id_compra', '=', 'c.id_compra')
            ->where('p.id_negocio', $businessId)
            ->select('p.*', 'u.nombre as cliente_nombre', 'u.apellido as cliente_apellido', 'u.email as cliente_email', 'e.codigo_entrega', 'e.id_entrega')
            ->orderByDesc('p.id_pedido')
            ->get();

        foreach ($orders as $order) {
            $order->items = DB::table('detalle_pedido as d')
                ->join('menu_items as m', 'm.id_menu_item', '=', 'd.id_menu_item')
                ->where('d.id_pedido', $order->id_pedido)
                ->select('d.*', 'm.nombre')
                ->get();
            $order->total = $order->items->sum(fn ($item) => (int) $item->valor * (int) $item->cantidad);

            if (empty($order->codigo_entrega)) {
                $order->codigo_entrega = sprintf('%06d', ($order->id_pedido * 265443) % 900000 + 100000);
            }
        }

        return view('business.orders', compact('orders'));
    }

    public function updateOrderStatus(Request $request, int $orderId): RedirectResponse
    {
        $businessId = $this->businessId($request);
        $order = DB::table('pedido as p')
            ->leftJoin('compra as c', 'c.id_pedido', '=', 'p.id_pedido')
            ->leftJoin('entrega as e', 'e.id_compra', '=', 'c.id_compra')
            ->where('p.id_pedido', $orderId)
            ->where('p.id_negocio', $businessId)
            ->select('p.*', 'e.codigo_entrega', 'e.id_entrega')
            ->first();
        abort_unless($order, 404);

        $action = $request->input('action', 'update_status');

        if ($action === 'mark_prepared') {
            // Estado 1 de cocina: Marcar plato como preparado
            DB::table('pedido')->where('id_pedido', $orderId)->update(['estado' => 'Preparado']);
            return back()->with('success', '🧑‍🍳 Pedido #' . $orderId . ' marcado como PREPARADO en cocina.');
        }

        if ($action === 'deliver_with_code') {
            // Estado 2: Ingresar código del repartidor y cambiar inmediatamente a entregado
            $expectedCode = !empty($order->codigo_entrega)
                ? trim((string) $order->codigo_entrega)
                : sprintf('%06d', ($orderId * 265443) % 900000 + 100000);

            $code = trim((string) $request->input('codigo_repartidor', ''));

            if ($code === '') {
                return back()->withErrors(['codigo_repartidor' => 'Por favor ingresa el código del repartidor.']);
            }

            if ($code !== $expectedCode && $code !== (string) $orderId) {
                return back()->withErrors(['codigo_repartidor' => 'El código no coincide con el del repartidor. Código esperado: ' . $expectedCode]);
            }

            DB::table('pedido')->where('id_pedido', $orderId)->update(['estado' => 'Entregado']);
            if (!empty($order->id_entrega)) {
                DB::table('entrega')->where('id_entrega', $order->id_entrega)->update([
                    'estado' => 'Entregado',
                    'fecha_entrega' => now(),
                    'fecha_confirmacion' => now(),
                ]);
            }

            return back()->with('success', '✅ ¡Código validado exitosamente! El Pedido #' . $orderId . ' cambió a ENTREGADO inmediatamente.');
        }

        if ($action === 'cancel_order') {
            DB::table('pedido')->where('id_pedido', $orderId)->update(['estado' => 'Cancelado']);
            return back()->with('success', 'Pedido #' . $orderId . ' cancelado.');
        }

        $data = $request->validate(['estado' => ['required', 'string']]);
        DB::table('pedido')->where('id_pedido', $orderId)->update(['estado' => $data['estado']]);

        return back()->with('success', 'Estado del pedido actualizado.');
    }

    public function clients(Request $request): View
    {
        $businessId = $this->businessId($request);
        $clients = DB::table('usuario as u')
            ->join('pedido as p', 'p.id_usuario', '=', 'u.id_usuario')
            ->join('detalle_pedido as d', 'd.id_pedido', '=', 'p.id_pedido')
            ->where('p.id_negocio', $businessId)
            ->select('u.id_usuario', 'u.nombre', 'u.apellido', 'u.email', 'u.direccion', 'u.ciudad', 'u.fecha_registro')
            ->selectRaw('COUNT(DISTINCT p.id_pedido) as total_pedidos')
            ->selectRaw('COALESCE(SUM(d.valor * d.cantidad), 0) as total_gastado')
            ->selectRaw('MAX(p.fecha_creacion) as ultima_compra')
            ->groupBy('u.id_usuario', 'u.nombre', 'u.apellido', 'u.email', 'u.direccion', 'u.ciudad', 'u.fecha_registro')
            ->orderByDesc('ultima_compra')
            ->get();

        return view('business.clients', compact('clients'));
    }

    public function notifications(): View
    {
        return view('business.notifications', [
            'notifications' => [
                ['title' => 'Panel conectado', 'body' => 'Tus datos se están leyendo desde la base compartida 2_shizen.', 'icon' => 'bx-check-circle', 'class' => 'green'],
                ['title' => 'Promociones disponibles', 'body' => 'Puedes activar promociones desde Productos.', 'icon' => 'bx-tag', 'class' => 'orange'],
            ],
        ]);
    }

    public function settings(Request $request): View
    {
        $business = DB::table('negocios')->where('id_negocio', $this->businessId($request))->first();
        abort_unless($business, 404);

        return view('business.settings', compact('business'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'min:2', 'max:100'],
            'direccion' => ['required', 'string', 'max:255'],
            'gmail_negocio' => ['required', 'email', 'max:150'],
        ]);
        DB::table('negocios')->where('id_negocio', $this->businessId($request))->update($data);
        $request->session()->put('business_user.negocio', $data['nombre']);

        return back()->with('success', 'Configuración actualizada correctamente.');
    }

    public function profile(Request $request): View
    {
        $userId = $request->session()->get('business_user.id_usuario');
        $businessId = $this->businessId($request);

        $user = DB::table('usuario')->where('id_usuario', $userId)->first();
        abort_unless($user, 404);

        $business = DB::table('negocios')->where('id_negocio', $businessId)->first();
        $kitchenUsers = DB::table('usuario')
            ->where(function ($q) {
                $q->where('rol', 'cocina')->orWhere('rol', 'Cocina');
            })
            ->where('id_cocina_negocio_asociado', $businessId)
            ->orderByDesc('id_usuario')
            ->get();

        return view('business.profile', compact('user', 'business', 'kitchenUsers'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'min:2', 'max:100'],
            'apellido' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
        ]);
        $userId = $request->session()->get('business_user.id_usuario');
        DB::table('usuario')->where('id_usuario', $userId)->update($data);
        $request->session()->put('business_user.nombre', trim($data['nombre'] . ' ' . $data['apellido']));

        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    public function createKitchen(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre'   => ['required', 'string', 'min:2', 'max:100'],
            'email'    => ['required', 'email', 'max:150', 'unique:usuario,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $businessId = $this->businessId($request);
        $parts = preg_split('/\s+/', trim($data['nombre']), 2);

        DB::table('usuario')->insert([
            'nombre' => $parts[0] ?? $data['nombre'],
            'apellido' => $parts[1] ?? 'Cocina',
            'email' => strtolower(trim($data['email'])),
            'password_hash' => Hash::make($data['password']),
            'rol' => 'cocina',
            'id_cocina_negocio_asociado' => $businessId,
        ]);

        return back()->with('success', '🧑‍🍳 Cuenta de Cocina (' . $data['email'] . ') registrada exitosamente.');
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        $data = $this->validatedProduct($request);
        $businessId = $this->businessId($request);
        $data['id_negocio'] = $businessId;
        $data['imagen_url'] = $this->handleImageUpload($request, null);
        $productId = DB::table('menu_items')->insertGetId($data);

        if (!empty($data['on_promo'])) {
            DB::table('promociones')->insert([
                'id_negocio'   => $businessId,
                'id_menu_item' => $productId,
                'nombre'       => $data['nombre'],
                'descripcion'  => $data['descripcion'] ?? null,
                'imagen_url'   => $data['imagen_url'] ?? null,
                'activo'       => 1,
            ]);
        }

        return back()->with('success', 'Producto creado correctamente.');
    }

    public function updateProduct(Request $request, int $productId): RedirectResponse
    {
        $data = $this->validatedProduct($request);
        $businessId = $this->businessId($request);
        $current = DB::table('menu_items')
            ->where('id_menu_item', $productId)
            ->where('id_negocio', $businessId)
            ->value('imagen_url');

        $data['imagen_url'] = $this->handleImageUpload($request, $current);

        $updated = DB::table('menu_items')
            ->where('id_menu_item', $productId)
            ->where('id_negocio', $businessId)
            ->update($data);
        abort_unless($updated, 404);

        if (!empty($data['on_promo'])) {
            DB::table('promociones')->updateOrInsert(
                ['id_menu_item' => $productId],
                [
                    'id_negocio'  => $businessId,
                    'nombre'      => $data['nombre'],
                    'descripcion' => $data['descripcion'] ?? null,
                    'imagen_url'  => $data['imagen_url'] ?? null,
                    'activo'      => 1,
                ]
            );
        } else {
            // Sale cuando ya no está en promo
            DB::table('promociones')->where('id_menu_item', $productId)->delete();
        }

        return back()->with('success', 'Producto actualizado correctamente.');
    }

    public function deleteProduct(Request $request, int $productId): RedirectResponse
    {
        $businessId = $this->businessId($request);
        DB::table('promociones')->where('id_menu_item', $productId)->delete();
        DB::table('menu_items')
            ->where('id_menu_item', $productId)
            ->where('id_negocio', $businessId)
            ->delete();

        return back()->with('success', 'Producto eliminado correctamente.');
    }

    public function togglePromotion(Request $request, int $productId): RedirectResponse
    {
        $product = DB::table('menu_items')
            ->where('id_menu_item', $productId)
            ->where('id_negocio', $this->businessId($request))
            ->first();
        abort_unless($product, 404);

        $active = ! (bool) $product->on_promo;
        DB::table('menu_items')->where('id_menu_item', $productId)->update([
            'on_promo' => $active,
            'precio_promocion' => $active ? ($product->precio_promocion ?: $product->precio) : null,
        ]);

        return back()->with('success', $active ? 'Promoción activada.' : 'Promoción desactivada.');
    }

    private function validatedProduct(Request $request): array
    {
        $validated = $request->validate([
            'nombre'       => ['required', 'string', 'min:2', 'max:100'],
            'descripcion'  => ['nullable', 'string'],
            'precio'       => ['required', 'integer', 'min:1000'],
            'stock'        => ['required', 'integer', 'min:0'],
            'imagen_url'   => ['nullable', 'url', 'max:255'],
            'id_categoria' => ['nullable', 'integer', 'exists:categorias,id_categoria'],
            'descuento_pct'=> ['nullable', 'integer', 'min:0', 'max:50'],
        ]);

        $pct   = (int) ($validated['descuento_pct'] ?? 0);
        $precio = (int) $validated['precio'];

        $validated['on_promo']        = $pct > 0 ? 1 : 0;
        $validated['precio_promocion'] = $pct > 0 ? (int) round($precio * (1 - $pct / 100)) : null;

        unset($validated['descuento_pct']);

        return $validated;
    }

    private function handleImageUpload(Request $request, ?string $currentUrl): ?string
    {
        if ($request->hasFile('imagen') && $request->file('imagen')->isValid()) {
            $file    = $request->file('imagen');
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

            if (! in_array($file->getMimeType(), $allowed, true)) {
                abort(422, 'Tipo de imagen no permitido. Usa JPG, PNG, WebP o GIF.');
            }
            if ($file->getSize() > 5 * 1024 * 1024) {
                abort(422, 'La imagen no debe superar los 5 MB.');
            }

            $uploadDir = base_path('../../shizen-home/public/images/catalogo/Imagenes_prueba');
            $filename  = 'producto_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($uploadDir, $filename);

            return '/shizen-home/public/images/catalogo/Imagenes_prueba/' . $filename;
        }

        // Si no se subio archivo, usar el campo oculto imagen_url o conservar el valor actual
        $postedUrl = trim((string) $request->input('imagen_url', ''));
        return $postedUrl !== '' ? $postedUrl : $currentUrl;
    }

    private function businessId(Request $request): int
    {
        return (int) $request->session()->get('business_user.id_negocio');
    }
}
