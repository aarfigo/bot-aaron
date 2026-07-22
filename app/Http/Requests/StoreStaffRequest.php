<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        $id = $this->route('staff') ?? null;
        return [
            'staffName' => 'required|string|max:191',
            'username' => 'required|string|max:100|unique:tbl_staff,username' . ($id ? ',' . $id . ',staffID' : ''),
            'password' => ($this->isMethod('post') ? 'required|' : 'nullable|') . 'string|min:6',
            // Use canonical Spanish role identifiers: admin, mesero, cocina_barra
            'role' => 'nullable|string|in:admin,mesero,cocina_barra',
            'status' => 'nullable|string|in:active,inactive',
        ];
    }
}
