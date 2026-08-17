<?php

namespace App\Http\Requests\Admin;

use App\Models\Customer;

class UpdateCustomerRequest extends AdminFormRequest
{
    protected ?string $openModal = 'customer-form';

    public function authorize(): bool
    {
        $customer = $this->route('customer');

        return $customer instanceof Customer
            && ($this->user()?->can('update', $customer) ?? false);
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
