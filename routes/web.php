<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BusinessController;

Route::get('/', function () {
    return redirect()->route('business.dashboard');
});

Route::get('/login', [BusinessController::class, 'showLogin'])->name('login');
Route::post('/login', [BusinessController::class, 'login'])->name('login.store');
Route::post('/logout', [BusinessController::class, 'logout'])->name('logout');

Route::middleware('business.auth')->group(function (): void {
    Route::get('/dashboard', [BusinessController::class, 'dashboard'])->name('business.dashboard');
    Route::get('/productos', [BusinessController::class, 'products'])->name('business.products');
    Route::get('/pedidos', [BusinessController::class, 'orders'])->name('business.orders');
    Route::post('/pedidos/{orderId}/estado', [BusinessController::class, 'updateOrderStatus'])->name('business.orders.status');
    Route::get('/clientes', [BusinessController::class, 'clients'])->name('business.clients');
    Route::get('/notificaciones', [BusinessController::class, 'notifications'])->name('business.notifications');
    Route::get('/configuracion', [BusinessController::class, 'settings'])->name('business.settings');
    Route::post('/configuracion', [BusinessController::class, 'updateSettings'])->name('business.settings.update');
    Route::get('/perfil', [BusinessController::class, 'profile'])->name('business.profile');
    Route::post('/perfil', [BusinessController::class, 'updateProfile'])->name('business.profile.update');
    Route::post('/perfil/cocina', [BusinessController::class, 'createKitchen'])->name('business.profile.kitchen');
    Route::post('/productos', [BusinessController::class, 'storeProduct'])->name('business.products.store');
    Route::put('/productos/{productId}', [BusinessController::class, 'updateProduct'])->name('business.products.update');
    Route::delete('/productos/{productId}', [BusinessController::class, 'deleteProduct'])->name('business.products.delete');
    Route::post('/productos/{productId}/promocion', [BusinessController::class, 'togglePromotion'])->name('business.products.promotion');
});
