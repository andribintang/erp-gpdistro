<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use Illuminate\Validation\Rule;

class StoreShipmentRequest extends AdminFormRequest
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
            'courier' => ['required', 'string', 'max:100'],
            'service' => ['nullable', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['pending', 'packed', 'shipped', 'delivered', 'returned'])],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
