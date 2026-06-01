<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends AdminFormRequest
{
    protected ?string $openModal = 'product-form';

    public function authorize(): bool
    {
        $product = $this->route('product');

        return $product instanceof Product
            && ($this->user()?->can('update', $product) ?? false);
    }

    public function rules(): array
    {
        /** @var Product $product */
        $product = $this->route('product');

        return [
            'sku' => ['required', 'string', 'max:80', Rule::unique('products', 'sku')->ignore($product->id)],
            'name' => ['required', 'string', 'max:255'],
            'product_type' => ['required', Rule::in(['apparel', 'spare_part', 'custom_service'])],
            'price' => ['required', 'numeric', 'min:0'],
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
