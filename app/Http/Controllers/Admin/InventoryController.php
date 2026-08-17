<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdjustInventoryRequest;
use App\Http\Requests\Admin\TransferInventoryRequest;
use App\Models\Inventory;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Repositories\Contracts\InventoryRepository;
use App\Repositories\Contracts\StockMovementRepository;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryRepository $inventories,
        private readonly StockMovementRepository $movements,
        private readonly InventoryService $inventoryService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Inventory::class);

        $filters = $request->only(['search', 'warehouse_id', 'low_stock', 'product_type', 'type', 'date_from', 'date_to']);
        $tab = $request->string('tab', 'stock')->toString();

        return view('admin.inventory.index', [
            'tab' => in_array($tab, ['stock', 'movements'], true) ? $tab : 'stock',
            'inventories' => $tab === 'stock'
                ? $this->inventories->paginate($filters)
                : null,
            'movements' => $tab === 'movements'
                ? $this->movements->paginate($filters)
                : null,
            'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(),
            'movementTypes' => StockMovement::query()->distinct()->pluck('type'),
            'lowStockCount' => $this->inventories->lowStockCount(),
        ]);
    }

    public function adjust(AdjustInventoryRequest $request, Inventory $inventory): RedirectResponse
    {
        $this->inventoryService->adjust(
            $inventory,
            $request->integer('quantity'),
            $request->string('notes')->toString(),
            $request->user(),
        );

        return back()->with('status', 'Stok berhasil disesuaikan.');
    }

    public function transfer(TransferInventoryRequest $request, Inventory $inventory): RedirectResponse
    {
        $this->inventoryService->transfer(
            $inventory,
            $request->integer('to_warehouse_id'),
            $request->integer('quantity'),
            $request->string('notes')->toString(),
            $request->user(),
        );

        return back()->with('status', 'Transfer stok antar gudang berhasil.');
    }
}
