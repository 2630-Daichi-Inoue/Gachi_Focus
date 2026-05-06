<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
                'string',
                'max:100'
            ],
            'reservation_id' => [
                'nullable',
                Rule::exists('reservations', 'id')->where('user_id', Auth::id())
            ]
        ];
    }
}
