<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends AdminFormRequest
{
    protected ?string $openModal = 'order-form';

    public function authorize(): bool
    {
        return $this->user()?->can('create', Order::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reserve_stock' => $this->boolean('reserve_stock'),
        ]);
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', Rule::exists('customers', 'id')],
            'channel' => ['nullable', Rule::in(['erp', 'web', 'marketplace'])],
            'reserve_stock' => ['nullable', 'boolean'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'warehouse_id' => ['nullable', Rule::exists('warehouses', 'id')],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'distinct', Rule::exists('products', 'id')],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
