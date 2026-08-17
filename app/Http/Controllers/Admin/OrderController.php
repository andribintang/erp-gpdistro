<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\StoreOrderPaymentRequest;
use App\Http\Requests\Admin\StoreOrderRequest;
use App\Http\Requests\Admin\StoreShipmentRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Warehouse;
use App\Repositories\Contracts\OrderRepository;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderService $orderService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);

        return view('admin.orders.index', [
            'orders' => $this->orders->paginate($request->only([
                'search', 'status', 'channel', 'date_from', 'date_to',
            ])),
            'customers' => Customer::query()->orderBy('name')->get(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function show(Order $order): View
    {
        $this->authorize('viewAny', Order::class);

        $order->load(['customer', 'items.product', 'payments', 'shipments']);

        return view('admin.orders.show', compact('order'));
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $order = $this->orderService->create($request->validated());

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', 'Sales order berhasil dibuat.');
    }

    public function storeCustomer(StoreCustomerRequest $request): RedirectResponse
    {
        Customer::query()->create($request->validated());

        return back()->with('status', 'Pelanggan berhasil ditambahkan.');
    }

    public function reserve(Order $order): RedirectResponse
    {
        $this->authorize('update', $order);
        $this->orderService->reserve($order);

        return back()->with('status', 'Stok berhasil direservasi untuk pesanan ini.');
    }

    public function storePayment(StoreOrderPaymentRequest $request, Order $order): RedirectResponse
    {
        $this->orderService->recordPayment($order, $request->validated());

        return back()->with('status', 'Pembayaran berhasil dicatat.');
    }

    public function storeShipment(StoreShipmentRequest $request, Order $order): RedirectResponse
    {
        $this->orderService->recordShipment($order, $request->validated());

        return back()->with('status', 'Data pengiriman berhasil disimpan.');
    }

    public function markPaid(Order $order): RedirectResponse
    {
        $this->authorize('update', $order);
        $this->orderService->markPaid($order);

        return back()->with('status', 'Pesanan ditandai lunas.');
    }

    public function process(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('process', $order);
        $this->orderService->process($order, $request->integer('warehouse_id') ?: null);

        return back()->with('status', 'Pesanan diproses dan stok dipotong.');
    }

    public function complete(Order $order): RedirectResponse
    {
        $this->authorize('update', $order);
        $this->orderService->complete($order);

        return back()->with('status', 'Pesanan diselesaikan.');
    }

    public function cancel(Order $order): RedirectResponse
    {
        $this->authorize('update', $order);
        $this->orderService->cancel($order);

        return back()->with('status', 'Pesanan dibatalkan.');
    }
}
