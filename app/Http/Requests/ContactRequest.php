<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'    => ['required','string','max:100'],
            'email'   => ['required','email','max:200'],
            'phone'   => ['nullable','string','max:50'],
            'message' => ['required','string','max:2000'],
        ];
    }
}
