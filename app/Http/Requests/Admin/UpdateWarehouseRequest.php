<?php

namespace App\Http\Requests\Admin;

use App\Models\Warehouse;
use Illuminate\Validation\Rule;

class UpdateWarehouseRequest extends AdminFormRequest
{
    protected ?string $openModal = 'warehouse-form';

    public function authorize(): bool
    {
        $warehouse = $this->route('warehouse');

        return $warehouse instanceof Warehouse
            && ($this->user()?->can('update', $warehouse) ?? false);
    }

    public function rules(): array
    {
        /** @var Warehouse $warehouse */
        $warehouse = $this->route('warehouse');

        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('warehouses', 'code')->ignore($warehouse->id)],
            'name' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
