<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;

class SupplierController extends Controller
{
    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return back()->with('status', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->authorize('delete', $supplier);

        if ($supplier->purchaseOrders()->exists()) {
            return back()->with('error', 'Supplier tidak dapat dihapus karena sudah memiliki purchase order.');
        }

        $supplier->delete();

        return back()->with('status', 'Supplier berhasil dihapus.');
    }
}
