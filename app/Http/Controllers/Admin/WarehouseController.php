<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWarehouseRequest;
use App\Http\Requests\Admin\UpdateWarehouseRequest;
use App\Models\Warehouse;
use App\Repositories\Contracts\WarehouseRepository;
use App\Services\WarehouseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function __construct(
        private readonly WarehouseRepository $warehouses,
        private readonly WarehouseService $warehouseService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Warehouse::class);

        return view('admin.warehouses.index', [
            'warehouses' => $this->warehouses->paginate($request->string('search')->toString()),
        ]);
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        $this->warehouseService->create($request->validated());

        return back()->with('status', 'Gudang berhasil ditambahkan.');
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        try {
            $this->warehouseService->update($warehouse, $request->validated());
        } catch (\InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('status', 'Gudang berhasil diperbarui.');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('delete', $warehouse);

        try {
            $this->warehouseService->delete($warehouse);
        } catch (\InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('status', 'Gudang berhasil dihapus.');
    }
}
