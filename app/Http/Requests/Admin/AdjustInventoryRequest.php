<?php

namespace App\Http\Requests\Admin;

use App\Models\Inventory;
class AdjustInventoryRequest extends AdminFormRequest
{
    protected ?string $openModal = 'inventory-adjust';

    public function authorize(): bool
    {
        return $this->user()?->can('adjust', $this->route('inventory')) ?? false;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'not_in:0'],
            'notes' => ['required', 'string', 'max:1000'],
        ];
    }
}
