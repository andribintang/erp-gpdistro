<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:Super Admin|Owner|Manager'])
    ->name('dashboard');

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'role:Super Admin|Owner|Manager'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::patch('/inventory/{inventory}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
        Route::patch('/inventory/{inventory}/transfer', [InventoryController::class, 'transfer'])->name('inventory.transfer');
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::patch('/orders/{order}/reserve', [OrderController::class, 'reserve'])->name('orders.reserve');
        Route::post('/orders/{order}/payments', [OrderController::class, 'storePayment'])->name('orders.payments.store');
        Route::post('/orders/{order}/shipments', [OrderController::class, 'storeShipment'])->name('orders.shipments.store');
        Route::patch('/orders/{order}/paid', [OrderController::class, 'markPaid'])->name('orders.paid');
        Route::patch('/orders/{order}/process', [OrderController::class, 'process'])->name('orders.process');
        Route::patch('/orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');
        Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/customers', [OrderController::class, 'storeCustomer'])->name('customers.store');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::get('/purchasing', [PurchaseOrderController::class, 'index'])->name('purchasing.index');
        Route::post('/purchasing', [PurchaseOrderController::class, 'store'])->name('purchasing.store');
        Route::patch('/purchasing/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchasing.approve');
        Route::patch('/purchasing/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchasing.receive');
        Route::post('/suppliers', [PurchaseOrderController::class, 'storeSupplier'])->name('suppliers.store');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
        Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
        Route::put('/warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
        Route::delete('/warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
