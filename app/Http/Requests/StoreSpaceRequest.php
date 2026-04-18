<?php

namespace App\Http\Requests;

use App\Rules\HalfHourTime;
use Illuminate\Foundation\Http\FormRequest;

class StoreSpaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
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
                'unique:spaces,name',
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
                new HalfHourTime,
            ],

            'close_time' => [
                'required',
                'date_format:H:i',
                'after:open_time',
                new HalfHourTime,
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
                'array'
            ],

            'amenities.*' => [
                'string',
                'exists:amenities,id'
            ],

            'image' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:1024',
            ],

            'is_public' => [
                'required',
                'boolean',
            ],
        ];
    }
}
