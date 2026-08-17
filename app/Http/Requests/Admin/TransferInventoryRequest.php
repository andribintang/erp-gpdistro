<?php

namespace App\Http\Requests\Admin;

use App\Models\Inventory;
use Illuminate\Validation\Rule;

class TransferInventoryRequest extends AdminFormRequest
{
    protected ?string $openModal = 'inventory-transfer';

    public function authorize(): bool
    {
        $inventory = $this->route('inventory');

        return $inventory instanceof Inventory
            && ($this->user()?->can('adjust', $inventory) ?? false);
    }

    public function rules(): array
    {
        return [
            'to_warehouse_id' => [
                'required',
                Rule::exists('warehouses', 'id')->where('is_active', true),
                Rule::notIn([$this->route('inventory')?->warehouse_id]),
            ],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['required', 'string', 'max:1000'],
        ];
    }
}
