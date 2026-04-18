<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
{
    public function timeValidator($datetime, $fail): void
    {
        $timeCollection = explode(':', $datetime);

        if ($timeCollection[1] !== '00' && $timeCollection[1] !== '30') {
            $fail('The time must be in 30-minute increments.');
        }

    }

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
                function ($attribute, $value, $fail) {
                    $this->timeValidator($value, $fail);
                },
            ]
        ];
    }
}
