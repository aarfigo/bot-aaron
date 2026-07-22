<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->can('create-orders');
    }

    public function rules()
    {
        return [
            'customer_table' => 'nullable|string|max:128',
            'total' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.itemID' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }
}
