<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

abstract class AdminFormRequest extends FormRequest
{
    protected ?string $openModal = null;

    protected function failedValidation(Validator $validator): void
    {
        if ($this->openModal) {
            session()->flash('open_modal', $this->openModal);
        }

        parent::failedValidation($validator);
    }
}
