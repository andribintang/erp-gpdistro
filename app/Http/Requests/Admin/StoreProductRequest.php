<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Validation\Rule;

class StoreProductRequest extends AdminFormRequest
{
    protected ?string $openModal = 'product-form';

    public function authorize(): bool
    {
        return $this->user()?->can('create', Product::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:80', Rule::unique('products', 'sku')],
            'name' => ['required', 'string', 'max:255'],
            'product_type' => ['required', Rule::in(['apparel', 'spare_part', 'custom_service'])],
            'price' => ['required', 'numeric', 'min:0'],
            'warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where('is_active', true)],
            'initial_stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
        ];
    }
}
