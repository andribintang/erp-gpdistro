<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdjustInventoryRequest;
use App\Models\Inventory;
use App\Repositories\Contracts\InventoryRepository;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(
        private readonly InventoryRepository $inventories,
        private readonly InventoryService $inventoryService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Inventory::class);

        return view('admin.inventory.index', [
            'inventories' => $this->inventories->paginate($request->string('search')->toString()),
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
}
