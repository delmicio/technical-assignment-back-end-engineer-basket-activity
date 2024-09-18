<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BasketRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'A user is required.',
            'user_id.exists' => 'The user does not exist.',
            'product_id.required' => 'A product is required.',
            'product_id.exists' => 'The product does not exist.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
