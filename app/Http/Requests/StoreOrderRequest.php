<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            // allow either 'table_number' (new UI) or legacy 'customer_table' if some clients still send it
            // Reverted to nullable so non-table orders (takeaway) and older clients keep working.
            'table_number' => 'nullable|integer|min:1',
            'items' => 'required|array|min:1',
            'items.*.itemID' => 'required|integer',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.comment' => 'nullable|string|max:255',
            'nombre' => 'required|string|max:100',
            'cedula' => 'required|string|max:40',
            'payment_method' => 'nullable|string|in:cash,pago_movil,pos,card,transfer,other',
            'payment_reference' => Rule::requiredIf(in_array($this->input('payment_method'), ['pago_movil', 'transfer'])),
        ];
    }

    /**
     * Normalize input before validation so legacy clients sending 'customer_table'
     * are supported and the controller always receives 'table_number'.
     */
    protected function prepareForValidation()
    {
        $input = $this->all();
        if (!$this->has('table_number') && $this->has('customer_table')) {
            $val = $this->input('customer_table');
            // coerce empty strings to null
            if (is_string($val)) $val = trim($val);
            $this->merge(['table_number' => $val]);
        }
    }
}
