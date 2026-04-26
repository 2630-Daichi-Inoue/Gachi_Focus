<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuestContactRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],
            'title' => [
                'required',
                'string',
                'max:50',
            ],
            'message' => [
                'required',
                'string',
                'max:1000',
            ],
        ];
    }
}
