<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSpaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    private function timeValidation($datetime, $fail): void
    {
        $time_collection = explode(':', $datetime);
        if ($time_collection[1] !== '00' && $time_collection[1] !== '30') {
            $fail('The time must be in 30-minute increments.');
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('spaces', 'name')->ignore($this->space),
            ],

            'prefecture' => [
                'required',
                'string',
                'max:20'
            ],

            'city' => [
                'required',
                'string',
                'max:50'
            ],

            'address_line' => [
                'required',
                'string',
                'max:255'
            ],

            'capacity' => [
                'required',
                'integer',
                'min:1'
            ],

            'open_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $this->timeValidation($value, $fail);
                },
            ],

            'close_time' => [
                'required',
                'date_format:H:i',
                'after:open_time',
                function ($attribute, $value, $fail) {
                    $this->timeValidation($value, $fail);
                },
            ],

            'weekday_price_yen' => [
                'required',
                'integer',
                'min:1'
            ],

            'weekend_price_yen' => [
                'required',
                'integer',
                'min:1'
            ],

            'description' => [
                'required',
                'string'
            ],

            'amenities' => [
                'nullable',
                'array',
            ],

            'amenities.*' => [
                'integer',
                'exists:amenities,id'
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:1024'
            ],
        ];
    }
}
