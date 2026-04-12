<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:50'
            ],
            'message' => [
                'required',
                'string'
            ],
            'reservation_id' => [
                'nullable',
                'exists:reservations,id'
            ]
        ];
    }
}
