<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceivePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('receive', $this->route('purchaseOrder')) ?? false;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where('is_active', true)],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_order_item_id' => ['required', 'distinct', Rule::exists('purchase_order_items', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
