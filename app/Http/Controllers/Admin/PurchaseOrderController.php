<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePurchaseOrderRequest;
use App\Http\Requests\Admin\ReceivePurchaseOrderRequest;
use App\Http\Requests\Admin\StoreSupplierRequest;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Repositories\Contracts\PurchaseOrderRepository;
use App\Services\PurchaseOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function __construct(
        private readonly PurchaseOrderRepository $purchaseOrders,
        private readonly PurchaseOrderService $purchaseOrderService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PurchaseOrder::class);

        return view('admin.purchasing.index', [
            'purchaseOrders' => $this->purchaseOrders->paginate($request->string('search')->toString()),
            'suppliers' => Supplier::query()->orderBy('name')->get(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StorePurchaseOrderRequest $request): RedirectResponse
    {
        $this->purchaseOrderService->create($request->validated());

        return back()->with('status', 'Purchase order berhasil dibuat.');
    }

    public function storeSupplier(StoreSupplierRequest $request): RedirectResponse
    {
        Supplier::query()->create($request->validated());

        return back()->with('status', 'Supplier berhasil ditambahkan.');
    }

    public function receive(ReceivePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->purchaseOrderService->receive($purchaseOrder, $request->validated());

        return back()->with('status', 'Penerimaan PO berhasil diposting ke inventori.');
    }

    public function approve(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorize('approve', $purchaseOrder);
        $this->purchaseOrderService->approve($purchaseOrder);

        return back()->with('status', 'Purchase order berhasil disetujui.');
    }
}
