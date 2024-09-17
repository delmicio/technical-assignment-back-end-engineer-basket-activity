<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BasketRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Authorize all users for now
    }

    public function rules()
    {
        return [
            'product_id' => 'required|exists:products,id',
        ];
    }
}
