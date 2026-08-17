<?php

namespace App\Providers;

use App\Repositories\Contracts\InventoryRepository;
use App\Repositories\Contracts\OrderRepository;
use App\Repositories\Contracts\ProductRepository;
use App\Repositories\Contracts\PurchaseOrderRepository;
use App\Repositories\Contracts\StockMovementRepository;
use App\Repositories\Contracts\WarehouseRepository;
use App\Repositories\EloquentInventoryRepository;
use App\Repositories\EloquentOrderRepository;
use App\Repositories\EloquentProductRepository;
use App\Repositories\EloquentPurchaseOrderRepository;
use App\Repositories\EloquentStockMovementRepository;
use App\Repositories\EloquentWarehouseRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(InventoryRepository::class, EloquentInventoryRepository::class);
        $this->app->bind(StockMovementRepository::class, EloquentStockMovementRepository::class);
        $this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
        $this->app->bind(ProductRepository::class, EloquentProductRepository::class);
        $this->app->bind(PurchaseOrderRepository::class, EloquentPurchaseOrderRepository::class);
        $this->app->bind(WarehouseRepository::class, EloquentWarehouseRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
