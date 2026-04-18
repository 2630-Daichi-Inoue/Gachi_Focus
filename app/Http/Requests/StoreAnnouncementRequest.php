<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
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
                'max:1000'
            ],
            'published_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'published_time' => [
                'required',
                'date_format:H:i',
            ],
            'expired_date' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:published_date',
            ],
            'expired_time' => [
                'nullable',
                'date_format:H:i',
            ]

        ];
    }
}
