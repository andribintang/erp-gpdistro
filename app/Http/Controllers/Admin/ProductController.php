<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use App\Models\Warehouse;
use App\Repositories\Contracts\ProductRepository;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly ProductService $productService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        return view('admin.products.index', [
            'products' => $this->products->paginate($request->string('search')->toString()),
            'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->productService->create($request->validated());

        return back()->with('status', 'Produk dan saldo awal berhasil ditambahkan.');
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->productService->update($product, $request->validated());

        return back()->with('status', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);
        $this->productService->delete($product);

        return back()->with('status', 'Produk berhasil dihapus atau dinonaktifkan.');
    }
}
