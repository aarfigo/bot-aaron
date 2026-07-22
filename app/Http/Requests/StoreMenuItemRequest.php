<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuItemRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'menuID' => 'required|integer|exists:tbl_menu,menuID',
            'menuItemName' => 'required|string|max:191',
            'price' => 'required|numeric|min:0',
        ];
    }
}
