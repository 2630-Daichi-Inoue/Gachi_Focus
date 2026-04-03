<?php

namespace App\Http\Requests;

use Illuminate\Container\Attributes\Auth;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
        $space = $this->route('space');

        return [
            'date' => [
                'required',
                'date',
            ],

            'start_at' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $this->timeValidation($value, $fail);
                },
            ],

            'end_at' => [
                'required',
                'date_format:H:i',
                'after:start_at',
                function ($attribute, $value, $fail) {
                    $this->timeValidation($value, $fail);
                },
            ],

            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:' . $space->capacity,
            ],

        ];
    }
}
