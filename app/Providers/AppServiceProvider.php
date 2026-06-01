<?php

namespace App\Providers;

use App\Repositories\Contracts\InventoryRepository;
use App\Repositories\Contracts\ProductRepository;
use App\Repositories\Contracts\PurchaseOrderRepository;
use App\Repositories\Contracts\WarehouseRepository;
use App\Repositories\EloquentInventoryRepository;
use App\Repositories\EloquentProductRepository;
use App\Repositories\EloquentPurchaseOrderRepository;
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
