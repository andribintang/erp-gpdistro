<?php

namespace App\Http\Requests\Admin;

class StoreSupplierRequest extends AdminFormRequest
{
    protected ?string $openModal = 'supplier-form';

    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Super Admin', 'Owner', 'Manager']) ?? false;
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
