<?php

namespace App\Http\Requests\Admin;

use App\Models\Supplier;
class UpdateSupplierRequest extends AdminFormRequest
{
    protected ?string $openModal = 'supplier-form';

    public function authorize(): bool
    {
        $supplier = $this->route('supplier');

        return $supplier instanceof Supplier
            && ($this->user()?->can('update', $supplier) ?? false);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
