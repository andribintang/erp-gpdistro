<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use Illuminate\Validation\Rule;

class StoreOrderPaymentRequest extends AdminFormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order instanceof Order
            && ($this->user()?->can('update', $order) ?? false);
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', Rule::in(['manual_transfer', 'midtrans', 'xendit', 'qris', 'virtual_account'])],
            'paid_at' => ['nullable', 'date'],
        ];
    }
}
