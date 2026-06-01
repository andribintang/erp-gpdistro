<?php

namespace App\Http\Requests\Admin;

use App\Models\Warehouse;
use Illuminate\Validation\Rule;

class StoreWarehouseRequest extends AdminFormRequest
{
    protected ?string $openModal = 'warehouse-form';

    public function authorize(): bool
    {
        return $this->user()?->can('create', Warehouse::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('warehouses', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
