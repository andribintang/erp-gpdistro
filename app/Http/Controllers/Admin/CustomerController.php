<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;

class CustomerController extends Controller
{
    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return back()->with('status', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('delete', $customer);

        if ($customer->orders()->exists()) {
            return back()->with('error', 'Pelanggan tidak dapat dihapus karena masih memiliki pesanan.');
        }

        $customer->delete();

        return back()->with('status', 'Pelanggan berhasil dihapus.');
    }
}
